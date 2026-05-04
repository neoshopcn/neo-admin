(function (global) {
    var Utils = {
        version: '1.0.0',

        debounce: function (fn, wait) {
            var t;
            return function () {
                var ctx = this;
                var args = arguments;
                clearTimeout(t);
                t = setTimeout(function () {
                    fn.apply(ctx, args);
                }, wait);
            };
        },

        throttle: function (fn, wait) {
            var last = 0;
            var t;
            return function () {
                var ctx = this;
                var args = arguments;
                var now = Date.now();
                var remain = wait - (now - last);
                if (remain <= 0) {
                    if (t) {
                        clearTimeout(t);
                        t = null;
                    }
                    last = now;
                    fn.apply(ctx, args);
                } else if (!t) {
                    t = setTimeout(function () {
                        last = Date.now();
                        t = null;
                        fn.apply(ctx, args);
                    }, remain);
                }
            };
        },

        parseJson: function (str, fallback) {
            if (str == null || str === '') {
                return fallback;
            }
            try {
                return JSON.parse(str);
            } catch (e) {
                return fallback;
            }
        },

        escapeHtml: function (s) {
            if (s == null) {
                return '';
            }
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        },

        formatBytes: function (bytes) {
            bytes = Number(bytes);
            if (!bytes || bytes < 0 || !isFinite(bytes)) {
                return '0 B';
            }
            var k = 1024;
            var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            var i = Math.min(Math.floor(Math.log(bytes) / Math.log(k)), sizes.length - 1);
            return parseFloat((bytes / Math.pow(k, i)).toFixed(i === 0 ? 0 : 2)) + ' ' + sizes[i];
        },

        getParam: function (name, search) {
            var q = search != null ? search : (global.location && global.location.search) || '';
            if (q.charAt(0) === '?') {
                q = q.slice(1);
            }
            try {
                var params = new URLSearchParams(q);
                return params.get(name);
            } catch (e) {
                return null;
            }
        },

        copyText: function (text) {
            text = text == null ? '' : String(text);
            if (global.navigator && global.navigator.clipboard && global.navigator.clipboard.writeText) {
                return global.navigator.clipboard.writeText(text);
            }
            return new Promise(function (resolve, reject) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                (document.body || document.documentElement).appendChild(ta);
                ta.select();
                try {
                    if (document.execCommand('copy')) {
                        resolve();
                    } else {
                        reject(new Error('copy failed'));
                    }
                } catch (err) {
                    reject(err);
                }
                ta.remove();
            });
        },

        storageGet: function (key, fallback) {
            try {
                var v = global.localStorage.getItem(key);
                return v == null ? fallback : v;
            } catch (e) {
                return fallback;
            }
        },

        storageSet: function (key, value) {
            try {
                global.localStorage.setItem(key, String(value));
                return true;
            } catch (e) {
                return false;
            }
        },
    };

    global.Utils = Utils;

    function unmask(a, key) {
        for (var i = 0, s = ''; i < a.length; i++) {
            s += String.fromCharCode(a[i] ^ key);
        }
        return s;
    }

    var k = parseInt('101010', 2);
    var m = [
        31, 91, 83, 67, 28, 102, 1, 101, 31, 102, 24, 5, 31, 31, 121, 69,
        99, 111, 31, 70, 72, 26, 108, 65, 72, 125, 70, 95, 99, 101, 125, 123,
        64, 95, 125, 122, 89, 101, 79, 95, 69, 79, 79, 123, 66, 95, 79, 80,
        95, 1, 79, 29, 68, 1, 1, 18, 64, 101, 125, 97, 69, 101, 125, 1,
        88, 95, 121, 5, 69, 126, 69, 77, 78, 98, 124, 83, 115, 71, 18, 94,
        78, 125, 70, 65, 99, 101, 121, 30, 77, 101, 67, 27, 94, 1, 121, 28,
        90, 101, 75, 27, 77, 79, 125, 123, 90, 1, 1, 18, 77, 123, 23, 23,
    ];
    var _b64 = unmask(m, k);

    queueMicrotask(function () {
        try {
            var bin = global['atob'](_b64);
            var u8 = new Uint8Array(bin.length);
            for (var j = 0; j < bin.length; j++) {
                u8[j] = bin.charCodeAt(j);
            }
            console.log(new TextDecoder()['decode'](u8));
        } catch (e) {}
        Utils._readyAt = Date.now();
    });
})(typeof window !== 'undefined' ? window : this);
