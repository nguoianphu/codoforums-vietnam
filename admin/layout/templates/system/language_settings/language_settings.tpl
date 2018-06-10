<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <li class="active"><i class="fa fa-language"></i> Language Settings</li>
    </ol>

</section>

<div class="row" style="">

    <div class="col-md-2">
        <div class="box box-info">
            <div class="box-body">
                <a class='btn btn-primary' href=" index.php?page=system/add_language"><i class="fa fa-plus"></i> Add
                    Language</a>
                {*<a class='btn btn-success' href="https://central.codologic.com/language/list_languages" target="_blank"><i*}
                {*class="fa fa-cloud-download"></i> Get more Languages</a>*}
            </div>
        </div>
    </div>
</div>

{if $dir_is_writable != true }
    <div class="row">
        <div class="col-md-10">
            <div class="alert alert-warning">
                <strong>Warning!</strong> {$dir} is not writable. you will not be able to add or edit languages.
            </div>
        </div>
    </div>
{/if}

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-body table-responsive">
                <table id="blocktable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Languages</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $language_value as $langName=>$langValue}
                        <tr>
                            <td>{$langName}</td>
                            <td>
                                <span class="">
                                    {if $langName !== 'en_US'}
                                        <a class='btn btn-success btn-flat btn-sm'
                                           href="index.php?page=system/edit_language&name={$langName}"><i
                                                    style="" class="fa fa-edit"></i> Edit</a>
                                    {/if}

                                    {if $default_language == $langName}
                                        <i class="fa fa-star-o"></i>

{else}

                                        <form action="index.php?page=system/default_language"
                                              style="display: inline-block;"
                                              method="post">
                                            <input style="display: none" type="text" name="language"
                                                   value="{$langName}"/>
                                            <input type="hidden" name="CSRF_token" value="{$token}"/>
                                            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-info btn-flat btn-sm" value="">
                                            <i class="fa fa-star-o"></i> Make Default
                                            </button>
                                        </form>
                                    {/if}

                                    {if $langName !== 'en_US'}
                                        <form action="index.php?page=system/delete_language"
                                              style="display: inline-block;"
                                              method="post" enctype="multipart/form-data">
                                            <input style="display: none" type="text" name="language"
                                                   value="{$langName}"/>
                                            <input type="hidden" name="CSRF_token" value="{$token}"/>
                                            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-flat btn-sm" value="">
                                            <i class="fa fa-trash-o"></i> Delete
                                            </button>
                                        </form>
                                        {*<form action="http://central.localhost/language/publish_language"*}
                                        {*role="form" style="display: inline-block;"*}
                                        {*target="_blank" method="post" enctype="multipart/form-data">*}
                                        {*<input style="display: none" type="text" name="language"*}
                                        {*value="{$langName}"/>*}
                                        {*<textarea style="display: none"*}
                                        {*name="languagetext">{$langValue}</textarea>*}
                                        {*<button type="submit" class="btn btn-primary btn-flat btn-sm" value="">*}
                                        {*<i class="fa fa-globe"></i> Share*}
                                        {*</button>*}
                                        {*</form>*}
                                    {/if}
                                </span>
                            </td>
                        </tr>
                    {/foreach}


                    </tbody>

                </table>
            </div>

        </div>


    </div>
</div>

