<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=system/language_settings"><i class="fa fa-language"></i> Language Settings</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-edit"></i>  Edit Language  </li>
    </ol>

</section>

{if isset($flash) && $flash['flash']==true}
    <div class="col-md-8">
        <div class="alert alert-success">
            {$flash['message']}
        </div>
    </div>
{/if}


<div class="col-md-10">
    <div  class="box box-info">
        <form onsubmit="return submitForm(this);" class="box-body" action="?page=system/edit_language&name={$name}" role="form" method="post" >
            <label>Edit Language</label><i> (Note: Careful with the spaces that are before or after the translation.)</i><br/>

            <div class="form-group"  id="block_html" >
                <label>Language JSON:</label><br>
                <textarea rows="5" id="block_html_tarea" name="language_json" placeholder="<!-- HTML CODE -->" class="form-control" >{$language_json|default:''|escape:'html'}</textarea>


                <div id="editor" style=" position: relative;height: 400px;">{$language_json|default:''|escape:'html'}</div>

            </div>
            <input type="hidden" name="CSRF_token" value="{$token}" />
            <input type="submit" value="Save"  class="btn btn-primary"/>
        </form>
        <br/>
        <br/>
    </div>
</div>

<script src="//cdn.jsdelivr.net/ace/1.1.7/min/ace.js" type="text/javascript" charset="utf-8"></script>
<script>

    function submitForm(el) {
      var val =  $('#block_html_tarea').val();
      console.log(val);
      if(isJson(val)){
        return true;
      }else{
        alert("Invalid JSON! Please correct the JSON.");
        return false;
      }
    }

  try {
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/chrome");
    editor.getSession().setMode("ace/mode/json");

    $('#block_html_tarea').hide();
    editor.getSession().on('change', function () {
      $('#block_html_tarea').val(editor.getSession().getValue());
    });

  }
  catch (e) {

    $('#editor').hide();
    $('#block_html_tarea').show();

  }

  function isJson(str) {
    try {
      JSON.parse(str);
    } catch (e) {
      return false;
    }
    return true;
  }

</script>