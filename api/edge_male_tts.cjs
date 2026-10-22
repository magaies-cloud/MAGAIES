// ============================================================================
// Microsoft Edge Neural Arabic Male Voice Generator (v2 Sec-MS-GEC DRM)
// Supports 100% Genuine Clear Male Neural Voices:
// - ar-EG-ShakirNeural (Egyptian Male)
// - ar-SA-HamedNeural  (Saudi / Sudanese-adapted Warm Male)
// - ar-AE-HamdanNeural (Gulf / Standard Clear Male)
// ============================================================================
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');
const https = require('https');
const WebSocket = require('ws');

const TRUSTED_CLIENT_TOKEN = '6A5AA1D4EAFF4E9FB37E23D68491D6F4';
const CHROMIUM_FULL_VERSION = '143.0.3650.75';
const CHROMIUM_MAJOR_VERSION = '143';
const SEC_MS_GEC_VERSION = `2-${CHROMIUM_FULL_VERSION}`;
const WIN_EPOCH = 11644473600;

const _SHA256_INITIAL = [
    0x6A09E667n, 0xBB67AE85n, 0x3C6EF372n, 0xA54FF53An,
    0x510E527Fn, 0x9B05688Cn, 0x1F83D9ABn, 0x5BE0CD19n,
];
const _SHA256_STATE = [
    0xFD9FBEA2n, 0x91243CE7n, 0xBE499094n, 0xCD2E1E0An,
    0x6C006F49n, 0x04BD35ABn, 0xFEB0A7E4n, 0x03EFFBD2n,
];
const _SHA256_SCHEDULE = [
    0xEB24BF9En, 0x1558691Fn, 0xA0C09EE2n, 0x0CFA32BAn,
    0xA622EE8Cn, 0xD1DD9491n, 0xC0DD25A0n, 0xF9E70AB6n,
    0xF0C31573n, 0x7058CE4En, 0x7188E606n, 0x00E5AB33n,
    0xD258B060n, 0x72766564n,
];
const _SHA256_ROUND_CONSTANTS = [
    0x428A2F98n, 0x71374491n, 0xB5C0FBCFn, 0xE9B5DBA5n, 0x3956C25Bn, 0x59F111F1n, 0x923F82A4n, 0xAB1C5ED5n,
    0xD807AA98n, 0x12835B01n, 0x243185BEn, 0x550C7DC3n, 0x72BE5D74n, 0x80DEB1FEn, 0x9BDC06A7n, 0xC19BF174n,
    0xE49B69C1n, 0xEFBE4786n, 0x0FC19DC6n, 0x240CA1CCn, 0x2DE92C6Fn, 0x4A7484AAn, 0x5CB0A9DCn, 0x76F988DAn,
    0x983E5152n, 0xA831C66Dn, 0xB00327C8n, 0xBF597FC7n, 0xC6E00BF3n, 0xD5A79147n, 0x06CA6351n, 0x14292967n,
    0x27B70A85n, 0x2E1B2138n, 0x4D2C6DFCn, 0x53380D13n, 0x650A7354n, 0x766A0ABBn, 0x81C2C92En, 0x92722C85n,
    0xA2BFE8A1n, 0xA81A664Bn, 0xC24B8B70n, 0xC76C51A3n, 0xD192E819n, 0xD6990624n, 0xF40E3585n, 0x106AA070n,
    0x19A4C116n, 0x1E376C08n, 0x2748774Cn, 0x34B0BCB5n, 0x391C0CB3n, 0x4ED8AA4An, 0x5B9CCA4Fn, 0x682E6FF3n,
    0x748F82EEn, 0x78A5636Fn, 0x84C87814n, 0x8CC70208n, 0x90BEFFFAn, 0xA4506CEBn, 0xBEF9A3F7n, 0xC67178F2n,
];
const _SHA256_ADJUSTMENT = 0xC9DC06A7n;
const _SHA256_BLOCK_CONSTANTS = [
    ..._SHA256_ROUND_CONSTANTS.slice(0, 14),
    _SHA256_ADJUSTMENT,
    ..._SHA256_ROUND_CONSTANTS.slice(15),
];
const _SHA256_SIGMA0_ADJUSTMENT = 0x059C0B80n;
const _SHA256_SIGMA1_ADJUSTMENT = 0x000B92C0n;

const _CS64_COEFFICIENTS = [
    [0xE82E91B1n, 0xB32700D0n],
    [0x44DE24C6n, 0x88DFF51Dn],
    [0x3BEB65BCn, 0x29514038n],
    [0xC89C5933n, 0xB328CBDFn],
    [0x8D1EC211n, 0xD405B4D5n],
    [0x314EF5D5n, 0x26E55F3Cn],
    [0xE92491B1n, 0x607801C6n],
    [0xF0A5B9D5n, 0x82B2C1FBn],
    [0x06064CF3n, 0xFA51A3DAn],
    [0xDA79FA60n, 0x207CC464n],
    [0xBED20560n, 0x3C3C74F3n],
    [0x89F9C9F8n, 0xD2D14B61n],
    [0x35FA38C6n, 0xBB04A2C1n],
    [0xCB49E3C6n, 0xC5626167n],
];
const _CS64_FACTOR = 0x82893933n;
const _CS64_ADJUSTMENT0 = 0xF2F90B1An;
const _CS64_ADJUSTMENT1 = 0x0B1A0000n;
const _U32_MASK = 0xFFFFFFFFn;

function _rol16(value) {
    const v = value & _U32_MASK;
    return ((v << 16n) | (v >> 16n)) & _U32_MASK;
}

function _rotr(v, n) {
    const x = v & _U32_MASK;
    return ((x >> BigInt(n)) | (x << BigInt(32 - n))) & _U32_MASK;
}

function _sigma0(value) {
    const v = value & _U32_MASK;
    return (_rotr(v, 7) ^ _rotr(v, 18) ^ (v >> 3n)) & _U32_MASK;
}

function _sigma1(value) {
    const v = value & _U32_MASK;
    return (_rotr(v, 17) ^ _rotr(v, 19) ^ (v >> 10n)) & _U32_MASK;
}

function _sha256_rounds(state, words, firstRound = 0) {
    let [a, b, c, d, e, f, g, h] = state;
    const constants = firstRound === 14 ? _SHA256_BLOCK_CONSTANTS : _SHA256_ROUND_CONSTANTS;
    for (let index = firstRound; index < 64; index++) {
        const S1 = _rotr(e, 6) ^ _rotr(e, 11) ^ _rotr(e, 25);
        const ch = g ^ (e & (f ^ g));
        const t1 = (h + S1 + ch + constants[index] + words[index]) & _U32_MASK;
        const S0 = _rotr(a, 2) ^ _rotr(a, 13) ^ _rotr(a, 22);
        const maj = (a & b) | (c & (a | b));
        const t2 = (S0 + maj) & _U32_MASK;
        h = g;
        g = f;
        f = e;
        e = (d + t1) & _U32_MASK;
        d = c;
        c = b;
        b = a;
        a = (t1 + t2) & _U32_MASK;
    }
    return [a, b, c, d, e, f, g, h];
}

function _expand_sha256_schedule(words, firstWord = 16) {
    for (let index = firstWord; index < 64; index++) {
        const s1 = _sigma1(words[index - 2]);
        const s0 = _sigma0(words[index - 15]);
        words.push((s1 + words[index - 7] + s0 + words[index - 16]) & _U32_MASK);
    }
}

function _sha256_init(data7) {
    const words = new Array(14).fill(0n);
    words.push(BigInt((data7[0] << 16) | (data7[1] << 8) | data7[2]));
    words.push(BigInt(data7.readUInt32BE(3)));
    for (let index = 16; index < 30; index++) {
        let value = _SHA256_SCHEDULE[index - 16];
        if (index - 2 >= 14) {
            value += index === 16 ? (_sigma1(words[index - 2]) ^ _SHA256_SIGMA1_ADJUSTMENT) : _sigma1(words[index - 2]);
        }
        if (index - 7 >= 14) {
            value += words[index - 7];
        }
        if (index - 15 >= 14) {
            value += index === 29 ? (_sigma0(words[index - 15]) ^ _SHA256_SIGMA0_ADJUSTMENT) : _sigma0(words[index - 15]);
        }
        words.push(value & _U32_MASK);
    }
    words.push(
        (_sigma1(words[28]) + words[23] + _sigma0(words[15]) + words[14] + _SHA256_ADJUSTMENT - _SHA256_ROUND_CONSTANTS[14]) & _U32_MASK
    );
    _expand_sha256_schedule(words, 31);
    const working = _sha256_rounds(_SHA256_STATE, words, 14);
    return _SHA256_INITIAL.map((x, i) => (x + working[i]) & _U32_MASK);
}

function _sha256_digest(dataBuf) {
    const length = 57 + dataBuf.length;
    const padZeros = ((55 - length) % 64 + 64) % 64;
    const tail = dataBuf.subarray(7);
    const padded = Buffer.alloc(tail.length + 1 + padZeros + 8, 0);
    tail.copy(padded, 0);
    padded[tail.length] = 0x80;
    padded.writeBigUInt64BE(BigInt(length * 8), padded.length - 8);

    let state = _sha256_init(dataBuf.subarray(0, 7));
    for (let offset = 0; offset < padded.length; offset += 64) {
        const words = [];
        for (let i = 0; i < 16; i++) {
            words.push(BigInt(padded.readUInt32BE(offset + i * 4)));
        }
        _expand_sha256_schedule(words, 16);
        const working = _sha256_rounds(state, words, 0);
        state = state.map((x, i) => (x + working[i]) & _U32_MASK);
    }
    const out = Buffer.alloc(32);
    for (let i = 0; i < 8; i++) {
        out.writeUInt32BE(Number(state[i]), i * 4);
    }
    return out;
}

function _words_le4_even(dataBuf) {
    const padLen = (4 - (dataBuf.length % 4)) % 4;
    const padded = padLen ? Buffer.concat([dataBuf, Buffer.alloc(padLen, 0)]) : dataBuf;
    const words = [];
    for (let i = 0; i < padded.length; i += 4) {
        words.push(BigInt(padded.readUInt32LE(i)));
    }
    if (words.length % 2 === 1) words.push(0n);
    return words;
}

function _cs64_digest(dataBuf, digestBuf) {
    const key_a = BigInt(digestBuf.readUInt32LE(0)) | 1n;
    const key_b = BigInt(digestBuf.readUInt32LE(4)) | 1n;
    const factors = [
        (key_a * _CS64_FACTOR) & _U32_MASK,
        (key_b * _CS64_FACTOR) & _U32_MASK,
    ];
    const terms = _CS64_COEFFICIENTS.map(([coeff], idx) => (coeff * factors[idx % 2]) & _U32_MASK);
    const offsets = _CS64_COEFFICIENTS.map(([, off]) => off);

    const leWords = _words_le4_even(Buffer.concat([Buffer.from([0x00]), dataBuf]));
    for (let index = 0; index < leWords.length; index++) {
        const word = leWords[index];
        let term = word * (index % 2 === 0 ? key_a : key_b);
        let offset = _rol16(word) * (index % 2 === 0 ? 0xEF0569FBn : 0xC31713DBn);
        if (index === 0) {
            term += _CS64_ADJUSTMENT0 * factors[0];
            offset += _CS64_ADJUSTMENT1;
        }
        terms.push(term & _U32_MASK);
        offsets.push(offset & _U32_MASK);
    }

    let swap_acc = 0n, swap_last = 0n, state = 0n, acc = 0n;
    for (let index = 0; index < terms.length; index += 2) {
        const x = terms[index], y = terms[index + 1];
        let t = _rol16((offsets[index] + x) & _U32_MASK);
        let q = (t * 0x76F50F34n) & _U32_MASK;
        swap_acc = (swap_acc + ((_rol16(q) * 0xC3EFEA97n + q) & _U32_MASK)) & _U32_MASK;

        t = _rol16((offsets[index + 1] + y) & _U32_MASK);
        q = (t * 0x1881CE3Cn) & _U32_MASK;
        swap_last = (_rol16(q) * 0x35BD1EC9n + q) & _U32_MASK;
        swap_acc = (swap_acc + swap_last) & _U32_MASK;

        state = (state * key_a + x) & _U32_MASK;
        state = (_rol16(state) * 0xCF98B111n) & _U32_MASK;
        state = (_rol16(state) * 0x87085B9Fn) & _U32_MASK;
        state = (_rol16(state) * 0x12CEB96Dn) & _U32_MASK;
        state = (_rol16(state) * 0x257E1D83n) & _U32_MASK;
        acc = (acc + state) & _U32_MASK;

        t = (state * key_b + y) & _U32_MASK;
        t = (_rol16(t) * 0xA27416F5n) & _U32_MASK;
        t = (_rol16(t) * 0xD38396FFn) & _U32_MASK;
        state = (_rol16((_rol16(t) * 0x7C932B89n) & _U32_MASK) * 0xBFA49F69n) & _U32_MASK;
        acc = (acc + state) & _U32_MASK;
    }
    const w0 = (swap_last ^ state) & _U32_MASK;
    const w1 = (swap_acc ^ acc) & _U32_MASK;
    const csBuf = Buffer.alloc(8);
    csBuf.writeUInt32LE(Number(w0), 0);
    csBuf.writeUInt32LE(Number(w1), 4);
    return csBuf.toString('hex').toUpperCase();
}

function generateSecMsGecV2(unixSeconds = Math.floor(Date.now() / 1000)) {
    let ticks = BigInt(Math.floor(unixSeconds)) + BigInt(WIN_EPOCH);
    ticks -= ticks % 300n;
    ticks *= 10000000n;
    const dataBuf = Buffer.from(`${ticks.toString()}${TRUSTED_CLIENT_TOKEN}`, 'ascii');
    const digestBuf = _sha256_digest(dataBuf);
    const cs64Hex = _cs64_digest(dataBuf, digestBuf);
    const sha256Hex = digestBuf.toString('hex').toUpperCase();
    return `${cs64Hex}-${sha256Hex}`;
}

const SKEW_CACHE_FILE = path.join(__dirname, '..', 'uploads', 'tts_cache', 'clock_skew.json');

function getRealUnixSeconds() {
    try {
        if (fs.existsSync(SKEW_CACHE_FILE)) {
            const data = JSON.parse(fs.readFileSync(SKEW_CACHE_FILE, 'utf8'));
            if (typeof data.skew === 'number' && Math.abs(Date.now() - data.savedAt) < 3600000) {
                return Promise.resolve(Math.floor(Date.now() / 1000) + data.skew);
            }
        }
    } catch (e) {}

    return new Promise((resolve) => {
        const req = https.request('https://www.bing.com/', { method: 'HEAD', timeout: 2000 }, (res) => {
            if (res.headers && res.headers.date) {
                const parsed = Date.parse(res.headers.date);
                if (!isNaN(parsed)) {
                    const realSec = Math.floor(parsed / 1000);
                    const localSec = Math.floor(Date.now() / 1000);
                    const skew = realSec - localSec;
                    try {
                        const dir = path.dirname(SKEW_CACHE_FILE);
                        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
                        fs.writeFileSync(SKEW_CACHE_FILE, JSON.stringify({ skew, savedAt: Date.now() }));
                    } catch (e) {}
                    return resolve(realSec);
                }
            }
            resolve(Math.floor(Date.now() / 1000));
        });
        req.on('error', () => resolve(Math.floor(Date.now() / 1000)));
        req.on('timeout', () => { req.destroy(); resolve(Math.floor(Date.now() / 1000)); });
        req.end();
    });
}

function attemptWsSynthesize(text, outFile, voice, rate, pitch, unixSec) {
    return new Promise((resolve, reject) => {
        const reqId = crypto.randomUUID().replace(/-/g, '');
        const secMsGec = generateSecMsGecV2(unixSec);
        const url = `wss://speech.platform.bing.com/consumer/speech/synthesize/readaloud/edge/v1?TrustedClientToken=${TRUSTED_CLIENT_TOKEN}&Sec-MS-GEC=${secMsGec}&Sec-MS-GEC-Version=${SEC_MS_GEC_VERSION}&ConnectionId=${reqId}`;

        const ws = new WebSocket(url, {
            headers: {
                'Pragma': 'no-cache',
                'Cache-Control': 'no-cache',
                'Origin': 'chrome-extension://jdiccldimpdaibmpdkjnbmckianbfold',
                'User-Agent': `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/${CHROMIUM_MAJOR_VERSION}.0.0.0 Safari/537.36 Edg/${CHROMIUM_MAJOR_VERSION}.0.0.0`,
                'Accept-Encoding': 'gzip, deflate, br, zstd',
                'Accept-Language': 'en-US,en;q=0.9',
                'Cookie': `muid=${crypto.randomBytes(16).toString('hex').toUpperCase()};`
            }
        });

        const audioBuffers = [];
        let done = false;
        const timer = setTimeout(() => {
            if (done) return;
            done = true;
            try { ws.close(); } catch (e) {}
            reject(new Error('Edge WebSocket Timeout'));
        }, 20000);

        ws.on('open', () => {
            const ts = new Date().toString();
            const configMsg = `X-Timestamp:${ts}\r\nContent-Type:application/json; charset=utf-8\r\nPath:speech.config\r\n\r\n{"context":{"synthesis":{"audio":{"metadataoptions":{"sentenceBoundaryEnabled":"true","wordBoundaryEnabled":"false"},"outputFormat":"audio-24khz-48kbitrate-mono-mp3"}}}}\r\n`;
            ws.send(configMsg);

            const cleanText = String(text)
                .replace(/\u064B/g, '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            const ssml = `<speak version='1.0' xmlns='http://www.w3.org/2001/10/synthesis' xml:lang='en-US'><voice name='${voice}'><prosody pitch='${pitch}' rate='${rate}' volume='+0%'>${cleanText}</prosody></voice></speak>`;
            const ssmlMsg = `X-RequestId:${ crypto.randomUUID().replace(/-/g, '') }\r\nContent-Type:application/ssml+xml\r\nX-Timestamp:${ts}Z\r\nPath:ssml\r\n\r\n${ssml}`;
            ws.send(ssmlMsg);
        });

        ws.on('message', (data, isBinary) => {
            if (isBinary) {
                const buf = Buffer.from(data);
                if (buf.length < 2) return;
                const headerLen = buf.readUInt16BE(0);
                if (buf.length > headerLen + 2) {
                    const headerStr = buf.subarray(2, headerLen + 2).toString('utf8');
                    if (headerStr.includes('Path:audio')) {
                        audioBuffers.push(buf.subarray(headerLen + 2));
                    }
                }
            } else {
                const str = data.toString('utf8');
                if (str.includes('Path:turn.end') && !done) {
                    done = true;
                    clearTimeout(timer);
                    try { ws.close(); } catch (e) {}
                    const finalBuf = Buffer.concat(audioBuffers);
                    if (finalBuf.length > 300) {
                        const dir = path.dirname(outFile);
                        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
                        fs.writeFileSync(outFile, finalBuf);
                        resolve(finalBuf.length);
                    } else {
                        reject(new Error('Empty audio buffer'));
                    }
                }
            }
        });

        ws.on('close', () => {
            if (!done) {
                done = true;
                clearTimeout(timer);
                reject(new Error('Edge WebSocket Closed Early'));
            }
        });

        ws.on('error', (err) => {
            if (!done) {
                done = true;
                clearTimeout(timer);
                reject(err);
            }
        });
    });
}

async function synthesizeEdgeMale(text, outFile, voice = 'ar-EG-ShakirNeural', rate = '+5%', pitch = '+0Hz') {
    const realSec = await getRealUnixSeconds();
    return await attemptWsSynthesize(text, outFile, voice, rate, pitch, realSec);
}

if (require.main === module) {
    const text = process.argv[2] || '';
    const outFile = process.argv[3] || '';
    const voice = process.argv[4] || 'ar-EG-ShakirNeural';
    const rate = process.argv[5] || '+5%';
    const pitch = process.argv[6] || '+0Hz';
    synthesizeEdgeMale(text, outFile, voice, rate, pitch)
        .then((bytes) => {
            console.log('OK_BYTES:' + bytes);
            process.exit(0);
        })
        .catch((err) => {
            console.error('FAIL:' + (err.message || err));
            process.exit(1);
        });
}

module.exports = { synthesizeEdgeMale, generateSecMsGecV2 };
