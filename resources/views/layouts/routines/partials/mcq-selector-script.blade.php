<script>

(function($){

'use strict';

function start($box){

  var $kind=$box.find('.mcq-kind'),$category=$box.find('.mcq-category'),$subjects=$box.find('.mcq-subject'),$chapters=$box.find('.mcq-chapter'),$count=$box.find('.mcq-available'),$add=$box.find('.mcq-add'),$selected=$box.find('.mcq-selected'),$input=$box.find('.mcq-selection-input'),$preview=$box.find('.routine-item-preview');

  var paths=[],legacyLines=[],token=0,ready=false,candidates=[];

  // Legacy rows stored only as text can also be removed one-by-one.

  legacyLines=($preview.val()||'').split(/\r?\n/).map(function(v){return v.trim();}).filter(Boolean);

  // Every selector has its own state and AJAX sequence; a zero count is not a disabled selection.

  try{paths=JSON.parse($input.val()||'[]');if(!Array.isArray(paths))paths=[];}catch(e){paths=[];}

  function key(p){return [p.kind,p.category_id,p.subject_id||0,p.chapter_id||0].join(':');}

  function label(p){return p.label||[p.kind==='department'?'Department':'Non-Department','Category #'+p.category_id,p.subject_id?'Subject #'+p.subject_id:'',p.chapter_id?'Chapter #'+p.chapter_id:''].filter(Boolean).join(' > ');}

  function render(){

    $selected.empty();

    paths.forEach(function(p,i){

      var $line=$('<div class="mcq-added-path">').css({background:'#f5f5f5',border:'1px solid #ddd',padding:'6px',marginBottom:'5px',wordBreak:'break-word'});

        var baseLabel=(p.label||label(p)).replace(/\s*\(Available Questions:.*?\)$/, '');

      var available=Number(p.available_question_count||0);

      var rangeText=(p.range_from && p.range_to)?' | Question Range: '+p.range_from+'-'+p.range_to+' ('+(Number(p.range_to)-Number(p.range_from)+1)+' questions)':'';

      $('<div>').addClass('mcq-path-label').text(baseLabel+' (Available Questions: '+available+')'+rangeText).appendTo($line);

      var $actions=$('<div>').css({marginTop:'5px',display:'flex',gap:'5px',flexWrap:'wrap'}).appendTo($line);

      $('<button type="button" class="btn btn-xs btn-info"><i class="fa fa-pencil"></i> Edit</button>').on('click',function(){

        $actions.hide();$editor.show();

      }).appendTo($actions);

      $('<button type="button" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</button>').on('click',function(){

        if(!window.confirm('এই Subject/Chapter নির্বাচনটি Delete করতে চান?'))return;

        paths.splice(i,1);render();

      }).appendTo($actions);

      var $editor=$('<div>').css({display:'none',marginTop:'7px',padding:'7px',background:'#fff',border:'1px solid #ddd'}).appendTo($line);

      $('<small>').text('Question Range (MCQ for Exam-এর এই level-এর ID ascending ক্রমে)').appendTo($editor);

      var $inputs=$('<div class="row">').appendTo($editor);

      var $fromWrap=$('<div class="col-xs-6">').appendTo($inputs),$toWrap=$('<div class="col-xs-6">').appendTo($inputs);

      var $from=$('<input type="number" min="1" step="1" placeholder="From (e.g. 1)" class="form-control input-sm">').val(p.range_from||'').appendTo($fromWrap);

      var $to=$('<input type="number" min="1" step="1" placeholder="To (e.g. 200)" class="form-control input-sm">').val(p.range_to||'').appendTo($toWrap);

      var $error=$('<small style="color:#dd4b39;display:block;margin:4px 0;">').appendTo($editor);

      $('<button type="button" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Update</button>').on('click',function(){

        var f=$from.val().trim(),t=$to.val().trim();

        if(!f&&!t){delete p.range_from;delete p.range_to;render();return;}

        var from=Number(f),to=Number(t);

        if(!/^\d+$/.test(f)||!/^\d+$/.test(t)||from<1||to<from||(available>0&&to>available)){

          $error.text('Valid From/To দিন। To Available Questions-এর বেশি হতে পারবে না।');return;

        }

        if(available===0){$error.text('এই Path-এ প্রশ্ন নেই; Question Range দেওয়া যাবে না।');return;}

        p.range_from=from;p.range_to=to;render();

      }).appendTo($editor);

      $('<button type="button" class="btn btn-xs btn-default" style="margin-left:5px;">Cancel</button>').on('click',function(){$editor.hide();$actions.show();}).appendTo($editor);

      $selected.append($line);

    });

    legacyLines.forEach(function(line,i){

      var $line=$('<div>').css({background:'#fff',border:'1px solid #ddd',padding:'5px',marginBottom:'4px',wordBreak:'break-word'});

      $line.append($('<span>').text(line+' '));

      $('<button type="button" class="btn btn-xs btn-danger pull-right"><i class="fa fa-trash"></i> Delete</button>').on('click',function(){legacyLines.splice(i,1);render();}).appendTo($line);

      $line.append($('<div class="clearfix">'));$selected.append($line);

    });

    $input.val(JSON.stringify(paths));

    $preview.val(legacyLines.concat(paths.map(function(p){

        var baseLabel=(p.label||label(p)).replace(/\s*\(Available Questions:.*?\)$/, '');

      return baseLabel+' (Available Questions: '+Number(p.available_question_count||0)+')'+(p.range_from&&p.range_to?' | Question Range: '+p.range_from+'-'+p.range_to:'');

    })).join('\n'));

  }

  function setOptions($el,data,placeholder){var keep=$el.val()||[];if(!Array.isArray(keep))keep=[keep];$el.empty();(data||[]).forEach(function(o){$el.append($('<option>').val(o.id).text(o.name));});$el.val(keep.filter(function(id){return $el.find('option[value="'+id+'"]' ).length;}));$el.trigger('change.select2');}

  function api(data){return $.ajax({url:$box.data('url'),dataType:'json',data:data});}

  function base(subject,chapter){return {kind:$kind.val(),category_id:$category.val(),subject_id:subject||'',chapter_id:chapter||''};}

  function resetSubjects(){setOptions($subjects,[]);resetChapters();}

  function resetChapters(){setOptions($chapters,[]);}

  function refreshCategories(){var id=++token;ready=false;$add.prop('disabled',true);$count.text('Available Questions: Loading...');api(base()).done(function(d){if(id!==token)return;setOptions($category,d.categories);refreshSubjects();}).fail(function(){if(id===token)$count.text('Unable to load categories');});}

  function refreshSubjects(){var id=++token;ready=false;$add.prop('disabled',true);resetSubjects();$count.text('Available Questions: Loading...');if(!$category.val()){$count.text('Available Questions: —');return;}api(base()).done(function(d){if(id!==token)return;setOptions($subjects,d.subjects);refreshChapters();}).fail(function(){if(id===token)$count.text('Unable to load subjects');});}

  function refreshChapters(){var id=++token,ids=$subjects.val()||[];ready=false;$add.prop('disabled',true);resetChapters();if(!$category.val()){$count.text('Available Questions: —');return;}if(!ids.length){refreshCounts();return;}$count.text('Available Questions: Loading...');$.when.apply($,ids.map(function(subject){return api(base(subject));})).done(function(){if(id!==token)return;var args=ids.length===1?[arguments]:Array.prototype.slice.call(arguments);var options=[];args.forEach(function(a,i){var d=a[0]||{};(d.chapters||[]).forEach(function(ch){options.push({id:ids[i]+':'+ch.id,name:$subjects.find('option[value="'+ids[i]+'"]').text()+' > '+ch.name});});});setOptions($chapters,options);refreshCounts();}).fail(function(){if(id===token)$count.text('Unable to load chapters');});}

  function makeCandidates(){var subjectIds=$subjects.val()||[],chapterValues=$chapters.val()||[],cat=$category.val(),kind=$kind.val(),catName=$category.find('option:selected').text(),map={};subjectIds.forEach(function(s){map[String(s)]=[];});chapterValues.forEach(function(v){var a=v.split(':');if(map[a[0]])map[a[0]].push(a[1]);});var arr=[];function add(s,ch){var text=(kind==='department'?'Department':'Non-Department')+' > '+catName;if(s)text+=' > '+$subjects.find('option[value="'+s+'"]').text();if(ch)text+=' > '+$chapters.find('option[value="'+s+':'+ch+'"]').text().split(' > ').slice(1).join(' > ');arr.push({kind:kind,category_id:Number(cat),subject_id:s?Number(s):null,chapter_id:ch?Number(ch):null,label:text});}if(!subjectIds.length)add(null,null);else subjectIds.forEach(function(s){if(map[String(s)].length)map[String(s)].forEach(function(ch){add(s,ch);});else add(s,null);});return arr;}

  function refreshCounts(){var id=++token;ready=false;$add.prop('disabled',true);if(!$category.val()){$count.text('Available Questions: —');return;}candidates=makeCandidates();$count.text('Available Questions: Loading...');$.when.apply($,candidates.map(function(p){return api(base(p.subject_id,p.chapter_id));})).done(function(){if(id!==token)return;var responses=candidates.length===1?[arguments]:Array.prototype.slice.call(arguments),total=0;responses.forEach(function(a,i){var d=a[0]||{};var n=Number(d.selection&&d.selection.available_question_count||0);candidates[i].available_question_count=n;total+=n;});ready=true;$count.text('Available Questions: '+total+' (sum of selected paths; repeated questions may overlap)');$add.prop('disabled',candidates.length===0);}).fail(function(){if(id===token)$count.text('Unable to calculate question count');});}

  $kind.on('change',function(){resetSubjects();$category.val('');refreshCategories();});

  $category.on('change',refreshSubjects);$subjects.on('change',refreshChapters);$chapters.on('change',refreshCounts);

  $add.on('click',function(){if(!ready)return;var added=0;candidates.forEach(function(p){if(paths.some(function(x){return key(x)===key(p);}))return;p.label+=' (Available Questions: '+p.available_question_count+')';paths.push(p);added++;});if(!added)alert('These Category / Subject / Chapter paths have already been added in this box. Select another path or remove the existing one.');render();});

  if($.fn.select2){[$category,$subjects,$chapters].forEach(function(el){el.select2({width:'100%',placeholder:el.data('placeholder')||undefined,closeOnSelect:!el.prop('multiple')});});}

  if(paths.length){legacyLines=[];} // Mapping rows are authoritative when present.

  render();refreshCategories();

}

$(function(){$('.routine-mcq-selector').each(function(){start($(this));});});

})(jQuery);

</script>
