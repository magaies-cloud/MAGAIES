import sharp from 'sharp';
import fs from 'fs';
import path from 'path';
import { execFileSync, execSync } from 'child_process';
import { fileURLToPath } from 'url';
import { createRequire } from 'module';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');

// Hard watchdog so this CLI helper can never hang indefinitely (12s allows dual-pass ara+eng on Linux VPS)
setTimeout(() => {
    console.log(JSON.stringify({ success: false, error: 'OCR CLI timeout' }));
    process.exit(1);
}, 12000).unref();

async function loadTesseract() {
    try {
        const mod = await import('tesseract.js');
        return mod.default || mod;
    } catch (e) {
        const altDir = path.join(rootDir, 'uploads', '.ocr_engine');
        const altPkg = path.join(altDir, 'node_modules', 'tesseract.js', 'package.json');
        if (fs.existsSync(altPkg)) {
            const reqAlt = createRequire(altPkg);
            return reqAlt('tesseract.js');
        }
        throw e;
    }
}

const imagePath = process.argv[2];
if (!imagePath || !fs.existsSync(imagePath)) {
    console.log(JSON.stringify({ success: false, error: 'Image not found' }));
    process.exit(1);
}

(async () => {
    try {
        // On Windows, prefer native Windows.Media.Ocr via win-ocr.ps1 for 100% spatial accuracy
        if (process.platform === 'win32') {
            const psScript = path.join(__dirname, 'win-ocr.ps1');
            if (fs.existsSync(psScript)) {
                try {
                    const out = execFileSync('powershell', [
                        '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass',
                        '-File', psScript, '-ImagePath', imagePath
                    ], { encoding: 'utf8', timeout: 8000 });
                    const m = out.match(/\{[\s\S]*\}/);
                    if (m) {
                        const parsed = JSON.parse(m[0]);
                        if (parsed && parsed.success) {
                            console.log(JSON.stringify(parsed));
                            process.exit(0);
                        }
                    }
                } catch (winErr) {}
            }
        }

        const meta = await sharp(imagePath).metadata();
        const origW = meta.width || 720;
        const origH = meta.height || 1280;
        const targetW = Math.min(Math.max(origW * 2, 1150), 1600);
        const scale = targetW / origW;

        const cleanLineStr = (s) => String(s || '')
            .replace(/[\u200B-\u200F\u202A-\u202E\u2066-\u2069]/g, '')
            .replace(/[|_—–]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();

        const extractStructuredLines = (resObj) => {
            const rawLines = [];
            if (Array.isArray(resObj?.data?.blocks)) {
                for (const b of resObj.data.blocks) {
                    for (const p of (b.paragraphs || [])) {
                        for (const ln of (p.lines || [])) {
                            rawLines.push(ln);
                        }
                    }
                }
            } else if (Array.isArray(resObj?.data?.lines)) {
                rawLines.push(...resObj.data.lines);
            }

            if (rawLines.length > 0) {
                const items = [];
                rawLines.forEach((ln, idx) => {
                    const txt = cleanLineStr(ln.text);
                    if (!txt) return;
                    const bbox = ln.bbox || {};
                    const x = Math.round((bbox.x0 || 0) / scale);
                    const y = Math.round((bbox.y0 || (idx * 35)) / scale);
                    const w = Math.round(((bbox.x1 || 0) - (bbox.x0 || 0)) / scale);
                    const h = Math.round(((bbox.y1 || 0) - (bbox.y0 || 0)) / scale) || 24;
                    items.push({ text: txt, rtl_text: txt, x, y, w, h });

                    if (Array.isArray(ln.words) && ln.words.length > 1) {
                        const sortedWords = [...ln.words]
                            .filter(wObj => cleanLineStr(wObj.text).length > 0 && wObj.bbox)
                            .sort((a, b) => (a.bbox.x0 || 0) - (b.bbox.x0 || 0));
                        if (sortedWords.length > 1) {
                            const clusters = [];
                            let cur = null;
                            for (const wObj of sortedWords) {
                                const wTxt = cleanLineStr(wObj.text);
                                const wx0 = Math.round((wObj.bbox.x0 || 0) / scale);
                                const wy0 = Math.round((wObj.bbox.y0 || 0) / scale);
                                const wx1 = Math.round((wObj.bbox.x1 || 0) / scale);
                                const wy1 = Math.round((wObj.bbox.y1 || 0) / scale);
                                if (!cur) {
                                    cur = { words: [wTxt], x0: wx0, y0: wy0, x1: wx1, y1: wy1 };
                                } else if (wx0 - cur.x1 > 32) {
                                    clusters.push(cur);
                                    cur = { words: [wTxt], x0: wx0, y0: wy0, x1: wx1, y1: wy1 };
                                } else {
                                    cur.words.push(wTxt);
                                    cur.x1 = Math.max(cur.x1, wx1);
                                    cur.y0 = Math.min(cur.y0, wy0);
                                    cur.y1 = Math.max(cur.y1, wy1);
                                }
                            }
                            if (cur) clusters.push(cur);
                            if (clusters.length > 1) {
                                for (const cl of clusters) {
                                    const clTxt = cl.words.join(' ').trim();
                                    const clRtl = [...cl.words].reverse().join(' ').trim();
                                    if (clTxt && clTxt !== txt) {
                                        items.push({
                                            text: clTxt,
                                            rtl_text: clRtl,
                                            x: cl.x0,
                                            y,
                                            w: Math.max(10, cl.x1 - cl.x0),
                                            h: Math.max(16, cl.y1 - cl.y0)
                                        });
                                    }
                                }
                            }
                        }
                    }
                });
                return items;
            }
            return String(resObj?.data?.text || '')
                .split(/\r?\n/)
                .map(l => cleanLineStr(l))
                .filter(Boolean)
                .map((text, idx) => ({ text, rtl_text: text, x: 0, y: idx * 35, w: origW, h: 24 }));
        };

        const Tesseract = await loadTesseract();
        const threshPng = await sharp(imagePath)
            .resize({ width: targetW, withoutEnlargement: false })
            .grayscale()
            .threshold(145)
            .png()
            .toBuffer();

        const hasUncompressedTrainedData = fs.existsSync(path.join(rootDir, 'ara.traineddata'));
        const workerOpts = { cachePath: rootDir, gzip: !hasUncompressedTrainedData, logger: () => {} };
        const araWorker = await Tesseract.createWorker(['ara', 'eng'], 1, workerOpts);
        await araWorker.setParameters({ tessedit_pageseg_mode: '6' });
        const araRes = await araWorker.recognize(threshPng, {}, { blocks: true });

        let arLines = extractStructuredLines(araRes);
        let enLines = [];
        let engWorker = null;

        const checkHasRefAndAmt = (lines) => {
            const joined = lines.map(l => l.text).join('\n');
            return /\b\d{6,19}\b/.test(joined) && (/\b\d{1,4}(?:,\d{3})*\.\d{2}\b/.test(joined) || /\b\d{3,10}\.\d{2}\b/.test(joined) || /SDG/i.test(joined));
        };

        if (!checkHasRefAndAmt(arLines)) {
            engWorker = await Tesseract.createWorker('eng', 1, workerOpts);
            await engWorker.setParameters({ tessedit_pageseg_mode: '6' });
            const engRes = await engWorker.recognize(threshPng, {}, { blocks: true });
            enLines = extractStructuredLines(engRes);
        }

        if (!checkHasRefAndAmt([...arLines, ...enLines])) {
            const normPng = await sharp(imagePath)
                .resize({ width: targetW, withoutEnlargement: false })
                .grayscale()
                .normalize()
                .png()
                .toBuffer();
            const araNorm = await araWorker.recognize(normPng, {}, { blocks: true });
            arLines = [...arLines, ...extractStructuredLines(araNorm)];
            if (!checkHasRefAndAmt([...arLines, ...enLines])) {
                if (!engWorker) {
                    engWorker = await Tesseract.createWorker('eng', 1, workerOpts);
                    await engWorker.setParameters({ tessedit_pageseg_mode: '6' });
                }
                const engNorm = await engWorker.recognize(normPng, {}, { blocks: true });
                enLines = [...enLines, ...extractStructuredLines(engNorm)];
            }
        }

        await araWorker.terminate();
        if (engWorker) await engWorker.terminate();

        console.log(JSON.stringify({
            success: true,
            img_w: origW,
            img_h: origH,
            ar_lines: arLines,
            en_lines: enLines
        }));
    } catch (err) {
        console.log(JSON.stringify({ success: false, error: err.message }));
        process.exit(1);
    }
})();
