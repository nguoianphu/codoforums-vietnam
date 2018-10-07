{* @CODOLICENSE *}
{* Smarty *}
{extends file='layout.tpl'}
{block name=body}

    <style type="text/css">

        .container {

            padding-top: 50px;
        }
    </style>

    <div class="container">


		<div class="row">


		</div>
	</div>

    <div id="FreiChatRootMountDiv"></div>



    <script type="text/javascript"
            src="freichat/client/main.php?id={$I->id}&xhash={$xhash}"></script>
    <link rel="stylesheet"
          href="freichat/client/m/public/freichat-mobile.bundle.css" type="text/css">
    <script type="text/javascript"
            src="freichat/client/api.js"></script>
    <script type="text/javascript"
            src="freichat/client/m/public/freichat-mobile.bundle.js"></script>


    <script type="text/javascript">


        freidefines.PLUGINS.showchatroom = 'disabled'; //Don't need it for pmx
    </script>

{/block}