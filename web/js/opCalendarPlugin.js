jQuery.noConflict();
var j$ = jQuery;

function loadPage(url, id)
{
  var h = j$(id).outerHeight();
  var w = j$(id).outerWidth();
  var loading = j$('<div class="loading">&lrm;</div>');
  loading.css('height', h);
  loading.css('width', w);
  j$(id).html(loading).fadeTo(0, 0.01).fadeTo('normal', 1);
  j$(id).load(url + ' ' + id, function() {
    j$(this).fadeTo(0, 0.01).fadeTo('normal', 1);
  });

  return false;
}
