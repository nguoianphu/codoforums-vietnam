{*

/*
* @CODOLICENSE
*/

*}

{*Smarty*}
<section class="content-header" id="breadcrumb_forthistemplate_hack">
    <h1>&nbsp;</h1>
    <ol class="breadcrumb" style="float:left; left:10px;">
        <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><i class="fa fa-gears"></i> Plugins</li>
    </ol>

</section>

<style>
    .plgenabled{

        background-color:#fff;

    }

    .plgdisabled{

        background-color:#fff;

    }

</style>

<div class="row">
    <div class="col-lg-12">

        <div class="table-responsive">
            <table class="table"  style="background: #fff;box-shadow: 1px 1px 1px #ccc">
                <thead>
                    <tr>
                        <th><a href="#">Plugin Name</a> </th>
                        <th><a href="#">Status</a> </th>
                        <th>Description</th>

                        <th>Version </th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$plugins item=plugin}
                        <tr class={$plugin.rowstyle}>
                            <td>

                                {$plugin.name} <br><br>

                                {if $plugin.admin eq true }

                                    <a href="index.php?page=ploader&plugin={$plugin.plg_name}">[Settings]</a>

                                {/if}
                            </td>
                            <td>
                                <form action="" method="post">
                                    <input type="hidden" name="plugin" value="{$plugin.plg_name}" />
                                    <input type="hidden" name="CSRF_token" value="{$token}" />

                                    {if $plugin.plg_status eq 1 }

                                        <input type="hidden" name="action" value="install" />
                                        <input type="submit" value="Install" class="btn btn-primary" />

                                    {else if $plugin.plg_status eq 2}

                                        <input type="hidden" name="action" value="enable" />
                                        <input type="submit" value="Enable" class="btn btn-default" />
                                    {else if $plugin.plg_status eq 4}

                                        <input type="hidden" name="action" value="upgrade" />
                                        <input type="submit" value="Upgrade" class="btn btn-success" />

                                    {else}  

                                        <input type="hidden" name="action" value="disable" />
                                        <input type="submit" value="Disable" class="btn" />

                                    {/if}


                                </form>  
                            </td>
                            <td>{$plugin.description}
                                <br>
                                <br>
                                <strong>Author:</strong> {$plugin.author} <br>
                                <strong>Website:</strong> <a target="_blank" href='{$plugin.author_url}'>{$plugin.author_url}</a>
                            </td>

                            <td>{$plugin.version}</td>

                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

    </div>
</div>