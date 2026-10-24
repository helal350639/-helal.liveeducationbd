/* LEBD-04: non-destructive Summernote equation tool. Saves TeX delimiters as text. */
(function (window, document) {
  'use strict';
  var activeEditor = null;
  var loading = null;
  var root = null;
  function buildModal() {
    if (root) return root;
    root = document.createElement('div');
    root.id = 'lebd-equation-dialog';
    root.style.cssText = 'display:none;position:fixed;inset:0;z-index:200000;background:rgba(0,0,0,.5);align-items:center;justify-content:center;padding:16px';
    root.innerHTML = '<div role="dialog" aria-modal="true" aria-label="Equation Editor" style="background:#fff;border-radius:8px;width:min(650px,100%);padding:20px;max-height:95vh;overflow:auto">' +
      '<h3 style="margin:0 0 14px">Equation Editor</h3><p style="color:#555">Write your formula using the math keyboard.</p>' +
      '<math-field id="lebd-equation-input" style="display:block;min-height:65px;width:100%;font-size:24px;border:1px solid #ccc;border-radius:5px;padding:10px"></math-field>' +
      '<p id="lebd-equation-error" role="alert" style="color:#b00;display:none;margin-top:10px"></p>' +
      '<div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px"><button type="button" id="lebd-equation-cancel" class="btn btn-default">Cancel</button>' +
      '<button type="button" id="lebd-equation-insert" class="btn btn-primary">Insert Equation</button></div></div>';
    document.body.appendChild(root);
    root.querySelector('#lebd-equation-cancel').addEventListener('click', close);
    root.querySelector('#lebd-equation-insert').addEventListener('click', insert);
    root.addEventListener('click', function(e){ if(e.target === root) close(); });
    document.addEventListener('keydown', function(e) {if(e.key === 'Escape' && root.style.display !== 'none') close();});
    return root;
  }
  function close(){if(root)root.style.display = 'none';}
  function ensureMathLive() {
    if (window.customElements && customElements.get('math-field')) return Promise.resolve();
    if (loading) return loading;
    loading = new Promise(function(resolve,reject){
      var s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/mathlive/mathlive.min.js';
      s.onload = function(){customElements.whenDefined('math-field').then(resolve,reject);};
      s.onerror = function(){reject(new Error('MathLive could not load. Check Internet connectivity.'));};
      document.head.appendChild(s);
    }).catch(function(e){loading=null;throw e;});
    return loading;
  }
  function open(editor){
    activeEditor = editor;
    var m = buildModal(); m.style.display = 'flex';
    var error = m.querySelector('#lebd-equation-error'); error.style.display='none';
    var field = m.querySelector('#lebd-equation-input');
    ensureMathLive().then(function(){field.value='';field.focus();}).catch(function(e){error.textContent=e.message;error.style.display='block';});
  }
  function insert(){
    var field = root.querySelector('#lebd-equation-input');
    if (!window.customElements.get('math-field')) return;
    var latex = String(field.getValue ? field.getValue('latex') : field.value || '').trim();
    if (!latex || !activeEditor || !window.jQuery) return;
    // Convert entities before putting untrusted equation text in a rich text HTML context.
    var safe = latex.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    var textarea = window.jQuery(activeEditor);
    try { textarea.summernote('focus'); textarea.summernote('pasteHTML', '<span class="lebd-tex">\\(' + safe + '\\)</span>&nbsp;'); close(); }
    catch (e) { root.querySelector('#lebd-equation-error').textContent='Unable to insert equation into the selected editor.'; root.querySelector('#lebd-equation-error').style.display='block'; }
  }
  function install(){
    if (!window.jQuery || !window.jQuery.fn.summernote) return;
    document.querySelectorAll('.note-editor').forEach(function(box){
      var toolbar = box.querySelector('.note-toolbar');
      if (!toolbar || toolbar.querySelector('.lebd-equation-button')) return;
      // Summernote renders editor just after its textarea.
      var previous = box.previousElementSibling;
      if(!previous || !previous.matches('textarea.editor')) return;
      var button = document.createElement('button');
      button.type='button'; button.className='btn btn-default btn-sm lebd-equation-button';
      button.textContent='∑ Equation'; button.title='Insert equation (MathLive)';
      button.style.margin='4px';
      button.addEventListener('click', function(e){e.preventDefault();open(previous);});
      toolbar.appendChild(button);
    });
  }
  document.addEventListener('DOMContentLoaded', function(){install();
    var observer = new MutationObserver(function(){install();});
    observer.observe(document.body,{childList:true,subtree:true});
  });
})(window,document);
