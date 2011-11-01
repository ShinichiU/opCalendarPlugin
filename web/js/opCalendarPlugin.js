jQuery.noConflict();
var j$ = jQuery;

function loadPage(url, id)
{
  j$(id).load(url + ' ' + id);

  return false;
}
