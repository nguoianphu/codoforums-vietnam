<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class=""><i class="fa fa-desktop"></i> System</li>            
        <li class="active"><i class="fa fa-clock-o"></i> Cron</li>
    </ol>

</section>

<style type="text/css">
    table .btn{
        padding: 1px 5px 1px;
        font-size: 10px;
        margin-right: -3px;
    }
</style>

<div class="row col-md-12">

    <p>Cron takes care of scheduling periodic tasks . </p>

    <div id="edit_cron" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Editing cron</h4>
                </div>
                <form action="{$self}?page=system/cron" method="POST">
                    <div class="modal-body">

                        <label>Cron Name</label>
                        <input type="text" name="name" id="cron_name" class="form-control" readonly="readonly"/>
                        <br/>
                        <label>Cron type</label>
                        <input type="text" name="type" id="cron_type" class="form-control" readonly="readonly"/>

                        <br/>
                        <label>Cron Interval</label>
                        <select class="form-control" name="e_interval">
                            <option value="3600">hourly</option>
                            <option value="86400">daily</option>
                            <option value="604800">weekly</option>
                            <option value="2592000">monthly</option>
                        </select><br/>
                        <p><b><u>Or</u></b> specify a custom value in seconds</p>
                        <input class="form-control" type="number" name="interval">
                        <input type="hidden" name="CSRF_token" value="{$token}" />
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>    </div>

    <table class="table" style="background: #fff;box-shadow: 1px 1px 1px #ccc">
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Interval</th>
            <th>Last started</th>
            <th>Last ended</th>
            <th>Status</th>
            <th></th>
        </tr>

        <tbody>

            {foreach from=$crons item=cron}
                <tr>
                    <td>{$cron.cron_name}</td>
                    <td id="type_{$cron.cron_name}">{$cron.cron_type}</td>
                    <td>{$cron.cron_interval}</td>
                    <td>{$cron.cron_started}</td>
                    <td>{$cron.cron_last_run}</td>
                    <td>{$cron.cron_status}</td>
                    <td><div id="edit_{$cron.cron_name}" class="btn btn-default edit">edit</div> &nbsp;|&nbsp; 
                        <div id="run_{$cron.cron_name}" class="btn btn-primary run"> run</div>
                    </td>

                {/foreach}            
        </tbody>
    </table>
</div>
<script type="text/javascript">

    jQuery(document).ready(function ($) {

        $('.edit').click(function () {

            var name = $(this).attr('id').replace("edit_", "");
            $('#cron_name').val(name);
            $('#cron_type').val($('#type_' + name).html());

            $('#edit_cron').modal();
        });

        $('.run').click(function () {

            var name = $(this).attr('id').replace("run_", "");

            setTimeout(function () {
                //window.location.reload(true)
            }, 1000);

            $.get('{$home}Ajax/cron/run/' + name, {
                token: '{$token}'
            }, function (data) {

                if (data != '') {

                    alert(data);
                }
            });


        });
    });
</script>