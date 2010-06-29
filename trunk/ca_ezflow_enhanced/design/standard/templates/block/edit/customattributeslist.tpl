{def $node_list = fetch( 'content', 'list', hash( 'parent_node_id', ezini( 'NodeSettings', 'RootNode', 'content.ini') ) )}

<select class="left block-control" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]">
    {foreach $node_list as $node}
        <option value="{$node.node_id}" {if eq( $block.custom_attributes[$custom_attrib], $node.node_id )}selected="selected"{/if}>{$node.name|wash}</option>
    {/foreach}
</select>

{undef $node_list}