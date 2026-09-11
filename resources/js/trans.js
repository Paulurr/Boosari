
function trans(key, replace = {}) {
    const value = key.split('.').reduce((obj, k) => (obj && typeof obj === 'object') ? obj[k] : undefined, window.i18n);

    if (typeof value !== 'string') {
        console.warn(`[trans] Clave de traducción no encontrada: "${key}"`);
        return key;
    }

    return Object.keys(replace).reduce(
        (str, param) => str.split(`:${param}`).join(replace[param]),
        value
    );
}

window.trans = trans;
