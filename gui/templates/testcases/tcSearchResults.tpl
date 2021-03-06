{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcSearchResults.tpl,v 1.6 2010/09/21 10:03:18 mx-julian Exp $
Purpose: smarty template - view test case in test specification

*}

{include file="inc_head.tpl" openHead='yes'}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {$tableID="$matrix->tableID"}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_js.tpl" bResetEXTCss=1}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

</head>

<h1 class="title">{$gui->pageTitle}</h1>

{include file="testcases/tcSearchGUI.inc.tpl"}

{if $gui->doSearch}
  <div class="workBack">
  {if $gui->warning_msg == ''}
    {foreach from=$gui->tableSet key=idx item=matrix}
      {$tableIDe="table_$idx"}
      {$matrix->renderBodySection($tableID)}
    {/foreach}
    <br />
    {lang_get s='generated_by_TestLink_on'} {$smarty.now|date_format:$gsmarty_timestamp_format}
  {else}
    <div class="user_feedback">
    <br />
    {$gui->warning_msg}
    </div>
  {/if}
{/if}    
</div>
</body>
</html>
