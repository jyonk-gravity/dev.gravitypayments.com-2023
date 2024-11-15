function spaiSendXHRCommand(command, element, nonce) {
   var xhr = new XMLHttpRequest(),
        formData = {
            'action':'shortpixel_ai_handle_page_action',
            'page': 'settings',
            'data[action]': command,
        },
        formString = '',
        url = typeof spai_settings === 'undefined' ? '/wp-admin/admin-ajax.php' : spai_settings.ajax_url;
    if(nonce){
        formData['spainonce'] = nonce;
    }
    for (var [key, value] of Object.entries(formData)) {
        formString += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(value);
    }
    xhr.onloadend = function() {
        var response = JSON.parse(xhr.response);
        if (response.success) {
            spaiActionResult(element, 'success');
        } else {
            spaiActionResult(element, 'error');
        }
    }
    xhr.open('post', url);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(formString);
}
function spaiActionResult(element, status) {
    element.closest('li').classList.remove("shortpixel_ai_processing");
    element.closest('li').classList.add("shortpixel_ai_" + status);
    setTimeout(function() {element.closest('li').classList.remove("shortpixel_ai_" + status); }, 1000);
}
function spaiLqipCacheClear(obj, nonce) {
    obj.closest('li').classList.add("shortpixel_ai_processing");
    spaiSendXHRCommand('clear lqip cache', obj, nonce);
}
function spaiCssCacheClear(obj, nonce) {
    obj.closest('li').classList.add("shortpixel_ai_processing");
    spaiSendXHRCommand('clear css cache', obj, nonce);
}