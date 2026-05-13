// Simple API adapter for frontend to call backend via the local proxy at /public/api.php
const API = {
  // request: { method, path, data }
  call: async function({method='GET', path='', data=null, headers={}}) {
    const url = '/public/api.php' + (path ? ('?path=' + encodeURIComponent(path)) : '');
    const opts = { method, headers: Object.assign({'Accept':'application/json'}, headers), credentials: 'include' };
    if (data) {
      if (opts.method.toUpperCase() === 'GET') {
        // append as querystring
      } else {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(data);
      }
    }
    const res = await fetch(url, opts);
    const contentType = res.headers.get('content-type') || '';
    if (contentType.includes('application/json')) return res.json();
    return res.text();
  }
};

export default API;
