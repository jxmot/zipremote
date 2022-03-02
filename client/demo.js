/*

    Author: https://github.com/jxmot
    Repository: https://github.com/jxmot/zipremote
*/

// show a "standby" message, in case down/up-load takes
// longer than expected.
function standby(action) {
    var msg = (action === 'get'? 'downloaded' : (action === 'put' ? 'uploaded' : 'UNKOWN'));
    $(`#results_${action}`).html(`Please wait, the zip file will be ${msg} soon.`);
}

// show the response
function itsdone(action, resp) {
    $(`#results_${action}`).html(`The reponse was: ${JSON.stringify(resp)}`);
};

function getdone(resp) {
    itsdone('get', resp);
};

function getZip() {
        // Edit as needed...
        var sid = 'bigsite';
        var key = 'DeL9ctT428Y2RSuC';
        var pid = 'plogs';
        var qry = `?siteid=${sid}&key=${key}&pathid=${pid}`;

        phpapi('GET', 'zipremapi', qry, getdone);
        standby('get');
};

function putdone(resp) {
    itsdone('put', resp);
};

function putZip() {
        // Edit as needed...
        var sid = 'bigsite';
        var key = 'DeL9ctT428Y2RSuC';
        var pid = 'uztest2';
        var zip = 'recursmall_sub.zip';
        var qry = `?siteid=${sid}&key=${key}&pathid=${pid}&zipname=${zip}`;

        phpapi('PUT', 'zipremapi', qry, putdone);
        standby('put');
};
