<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-flag"></i> Forum Reports</li>
    </ol>


</section>
<div class="col-md-12">

    <div class="box box-info">

        <h3 class="col-md-12">Open reports </h3>  
        <br/>            
        <div class="box-body table-responsive">

            <table id="blocktable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Topic</th>
                        <th>Reason</th>                        
                        <th>No. of times reported</th>

                    </tr>
                </thead>
                <tbody id="selections">

                    {foreach from=$reports item=report}
                        <tr>
                            <td><input class="report_cb" data-id="{$report.id}" type="checkbox" /></td>
                            <td><a target="_blank" href="{$report.href}">{$report.title}</a></td>
                            <td>{$report.reason|escape}</td>
                            <td>{$report.num_reports}</td>

                        </tr>
                    {/foreach}
                </tbody>
            </table>

            <hr/>

            <div class="btn-group">
                <button id="action_selector" type="button" class="btn btn-default dropdown-toggle disabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Select an action <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a onclick="javascript:actOnreport('close')" href="#">Ignore and close report</a></li>
                    <li><a href="#">Send pm to author</a></li>
                    <li><a href="#">Close topic</a></li>
                    <li><a href="#">Delete topic</a></li>
                </ul>
            </div>

        </div>

    </div><!-- /.box-body -->
    <br/>

    <form id="action_report_form" action="index.php?page=moderation/reports" role="form" method="post" enctype="multipart/form-data">

        <input type="hidden" name="CSRF_token" value="{$token}" />
        <input type="hidden" name="action_type" value="" />
        <input id="report_id" type="hidden" name="report_id" value="{$token}" />

    </form>

    <div class="box box-info">

        <h3 class="col-md-12">Closed reports </h3>   
        <br/>            
        <div class="box-body table-responsive">

            <table id="blocktable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>No. of times reported</th>

                    </tr>
                </thead>
                <tbody>

                    {foreach from=$closed item=report}
                        <tr>
                            <td><a target="_blank" href="{$report.href}">{$report.title}</a></td>
                            <td>{$report.num_reports}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div><!-- /.box-body -->




</div>

<script type="text/javascript">

    jQuery(function ($) {

        $('.report_cb').on('ifChanged', function () {

            if ($('#selections .report_cb:checked').length > 0) {

                $('#action_selector').removeClass('disabled');
            } else {

                $('#action_selector').addClass('disabled');
            }
        });
    });

    function getCheckedReportIds() {

        return $('#selections .report_cb:checked').map(
                function () {
                    return this.dataset.id;
                }).get().join(",");
    }

    function actOnreport(action) {

        var ids = getCheckedReportIds()
        document.getElementById('report_id').value = ids;
        document.getElementById('action_type').value = action;        
        document.getElementById('close_report_form').submit();
    }

</script>