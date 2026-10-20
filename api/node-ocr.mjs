import Tesseract from 'tesseract.js';
import sharp from 'sharp';
import fs from 'fs';
import path from 'path';
import { execFileSync } from 'child_process';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

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

        const cleanPng = await sharp(imagePath)
            .resize({ width: origW * 2, withoutEnlargement: false })
            .grayscale()
            .normalize()
            .png()
            .toBuffer();

        const [engWorker, araWorker] = await Promise.all([
            Tesseract.createWorker('eng'),
            Tesseract.createWorker(['ara', 'eng'])
        ]);

        await engWorker.setParameters({ tessedit_pageseg_mode: '6' });
        await araWorker.setParameters({ tessedit_pageseg_mode: '6' });

        const [engRes, araRes] = await Promise.all([
            engWorker.recognize(cleanPng),
            araWorker.recognize(cleanPng)
        ]);

        await Promise.all([engWorker.terminate(), araWorker.terminate()]);

        const extractStructuredLines = (resObj, isRtl = false) => {
            const lines = resObj?.data?.lines || [];
            if (lines.length > 0) {
                return lines.map((ln, idx) => {
                    const txt = String(ln.text || '').replace(/[|_—–]+/g, ' ').replace(/\s+/g, ' ').trim();
                    if (!txt) return null;
                    const bbox = ln.bbox || {};
                    const x = Math.round((bbox.x0 || 0) / 2);
                    const y = Math.round((bbox.y0 || (idx * 35)) / 2);
                    const w = Math.round(((bbox.x1 || 0) - (bbox.x0 || 0)) / 2);
                    const h = Math.round(((bbox.y1 || 0) - (bbox.y0 || 0)) / 2) || 24;
                    return { text: txt, rtl_text: txt, x, y, w, h };
                }).filter(Boolean);
            }
            return String(resObj?.data?.text || '')
                .split(/\r?\n/)
                .map(l => l.trim())
                .filter(Boolean)
                .map((text, idx) => ({ text, rtl_text: text, x: 0, y: idx * 35, w: origW, h: 24 }));
        };

        console.log(JSON.stringify({
            success: true,
            img_w: origW,
            img_h: origH,
            ar_lines: extractStructuredLines(araRes, true),
            en_lines: extractStructuredLines(engRes, false)
        }));
    } catch (err) {
        console.log(JSON.stringify({ success: false, error: err.message }));
        process.exit(1);
    }
})();
