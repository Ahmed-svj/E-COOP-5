/* Uploaded report files: kept in this browser's IndexedDB so a supervisor on the same device can open them. */
var FileStore = (function () {
  var dbp = null;
  function db() {
    if (!dbp) dbp = new Promise(function (res, rej) {
      try {
        var r = indexedDB.open('easy-coop-files', 1);
        r.onupgradeneeded = function () { r.result.createObjectStore('files'); };
        r.onsuccess = function () { res(r.result); };
        r.onerror = function () { rej(r.error); };
      } catch (e) { rej(e); }
    });
    return dbp;
  }
  function tx(mode, fn) {
    return db().then(function (d) {
      return new Promise(function (res, rej) {
        var t = d.transaction('files', mode), st = t.objectStore('files'), out = fn(st);
        t.oncomplete = function () { res(out && 'result' in out ? out.result : undefined); };
        t.onerror = function () { rej(t.error); };
        t.onabort = function () { rej(t.error); };
      });
    });
  }
  return {
    put: function (key, file) { return tx('readwrite', function (st) { return st.put({ name: file.name, type: file.type, blob: file }, key); }); },
    get: function (key) { return tx('readonly', function (st) { return st.get(key); }); }
  };
})();

/* load a viewer library once, trying jsDelivr then cdnjs */
var LibLoader = (function () {
  var cache = {};
  function one(src) {
    return new Promise(function (res, rej) {
      var s = document.createElement('script'); s.src = src; s.async = true;
      s.onload = function () { res(); }; s.onerror = function () { s.remove(); rej(new Error('load ' + src)); };
      document.head.appendChild(s);
    });
  }
  function any(srcs, check) {
    var i = 0;
    function next() { if (check()) return Promise.resolve(); if (i >= srcs.length) return Promise.reject(new Error('unavailable')); return one(srcs[i++]).then(function () { if (!check()) return next(); }, next); }
    return next();
  }
  return {
    pdf: function () {
      if (!cache.pdf) cache.pdf = any([
        'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js'
      ], function () { return !!window.pdfjsLib; }).then(function () {
        return any([
          'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js',
          'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'
        ], function () { return !!window.pdfjsWorker; });
      }).then(function () {
        try { window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js'; } catch (e) {}
        return window.pdfjsLib;
      });
      cache.pdf.catch(function () { cache.pdf = null; });
      return cache.pdf;
    },
    docx: function () {
      if (!cache.docx) cache.docx = any([
        'https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js'
      ], function () { return !!window.mammoth; }).then(function () { return window.mammoth; });
      cache.docx.catch(function () { cache.docx = null; });
      return cache.docx;
    }
  };
})();
