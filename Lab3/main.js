function curataDocumentul() {
    const linkuri = document.querySelectorAll('a[href^="http://www.scs.ubbcluj.ro"]');
    linkuri.forEach(link => link.remove());
    const container = document.createElement('div');
    container.style.cssText = "margin-top: 40px; padding: 20px; border-top: 3px solid #333; background: #fafafa;";
    const titlu = document.createElement('h3');
    titlu.textContent = "Output:";
    const pre = document.createElement('pre');
    pre.style.cssText = "background: #eee; padding: 15px; border: 1px solid #ccc; overflow-x: auto;";
    pre.textContent = document.documentElement.outerHTML;
    container.appendChild(titlu);
    container.appendChild(pre);
    document.body.appendChild(container);
}

window.addEventListener('load', curataDocumentul);
