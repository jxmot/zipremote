/*
    Send a request with arguments(optional) and
    invoke a callback function when completed.

    This function is typically used when invoking
    a local PHP endpoint. The `func` argument is 
    is the name of the endpoint file.

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/
function phpapi(meth, func, qry, callback) {
    var _resp = {};
    var resp =  null;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if(this.readyState == 4) {
            resp = this.responseText;
            resp = resp.replace(/[\n\r]/g, '');
            // detect if the string is JSON, if yes 
            // then turn it into an object, otherwise
            // leave it alone.
            if(resp.match(/^\{/g)) _resp = JSON.parse(resp);
            else _resp = resp;

            callback(_resp);
        }
    };
    if((qry === null) || (qry === '')) {
        xmlhttp.open(meth, `./${func}.php`, true);
    } else {
        xmlhttp.open(meth, `./${func}.php${qry}`, true);
    }
    xmlhttp.send();
};
