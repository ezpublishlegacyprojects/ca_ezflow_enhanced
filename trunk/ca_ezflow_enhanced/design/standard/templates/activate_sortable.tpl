{def $content_object = $node.object}
{if $content_object.can_edit}
	{ezscript_require( array('interface.js','sortable.js') )}
	{ezcss_require(array('sortable.css'))}
{/if}