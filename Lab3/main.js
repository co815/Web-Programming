const fs = require('fs');

let html = fs.readFileSync('test.html', 'utf8');

const pattern = /<a [^>]*href="http:\/\/www\.scs\.ubbcluj\.ro[^"]*"[^>]*>.*?<\/a>/gs;

const rezultat = html.replace(pattern, '');

fs.writeFileSync('output.html', rezultat);
