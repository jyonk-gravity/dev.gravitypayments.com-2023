/*
 * function to escape special characters specially when dealing with json data
 */
String.prototype.escapeSpecialChars = function () {
    return this.replace(/\\n/g, '\\n')
        .replace(/\\'/g, '\\\'')
        .replace(/\\"/g, '\\"')
        .replace(/\\&/g, '\\&')
        .replace(/\\r/g, '\\r')
        .replace(/\\t/g, '\\t')
        .replace(/\\b/g, '\\b')
        .replace(/\\f/g, '\\f');
};