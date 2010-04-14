<?php

include_once( 'kernel/classes/ezcontentcache.php' );

/**
 * Implements methods called remotely by sending XHR calls
 * 
 */
class eZFlowEnhancedServerCallFunctions
{
    /**
     * comment
     * 
     * @param mixed $args
     * @return array
     */
    public static function saveState( $args )
    {
        $http = eZHTTPTool::instance();
        $serializedObjects = $http->postVariable( 'serializedObjects' );
        
        $splitZoneId = explode('--',$serializedObjects[0]['id']);
        $zoneDigit = $splitZoneId[0];
        $contentObjectAttributeID = $splitZoneId[2];
        $version = $splitZoneId[3];
        
        $contentObjectAttribute = eZContentObjectAttribute::fetch( $contentObjectAttributeID, $version );
        $contentObject = $contentObjectAttribute->object();
        
        if ( $contentObjectAttribute )
            $page = $contentObjectAttribute->content();
        
        $existingBlocks = self::getExistingBlocs( $page );
        
        // reorder zone to deal with transfer of blocks
        $newBlockZone = array();
        $otherZones = array();
        foreach( $serializedObjects as $zone)
        {
            $zoneId = $zone['id'];
            $blockList = $zone['o'][$zoneId];
            $blockIdList = array();
            foreach( $blockList as $block )
            {
                $splitName = explode('-',$block);
                $blockIdList[] = $splitName[2];
            }
            $splitZoneId = explode('--',$zoneId);
            $zoneDigit = $splitZoneId[0];
            $zoneIdentifier = $splitZoneId[1];
            $contentObjectAttribute_id = $splitZoneId[2];
            $version = $splitZoneId[3];
            
            // check if there is new block in this zone
            if ( count($existingBlocks[$zoneIdentifier]) < count($blockList) )
            {
                $newBlockZone[] = array(
                                            'digit_id' => $zoneDigit,
                                            'zone_identifier' => $zoneIdentifier,
                                            'blockList' => $blockIdList
                                        );
            }
            else
            {
                $otherZones[] = array(
                                            'digit_id' => $zoneDigit,
                                            'zone_identifier' => $zoneIdentifier,
                                            'blockList' => $blockIdList
                                        );
            }
        }    
        
        // Deal with zone with new bloc
        foreach( $newBlockZone as $zone )
        {
            //look for original block to copy it
            $blockToCopy = false;
            $zones = $page->attribute('zones');
            foreach( $zones as $zoneIndex => $existingZone )
            {
                if ( $existingZone->attribute('zone_identifier') == $zone['zone_identifier'] )
                    continue;
                $blocks = $existingZone->attribute('blocks');
                foreach( $blocks as $index => $block )
                {
                    if ( in_array($block->attribute('id') ,$zone['blockList']) )
                    {
                        $blockToCopy = $block;
                        break;
                    }
                }
                if ($blockToCopy)
                    break;
            }
            
            $currentZone = $page->getZone( $zone['digit_id'] );
            $blockToCopy->setAttribute('zone_id',$currentZone->attribute('id'));
            
            $currentZone->addBlock($blockToCopy);
            
            self::updateblockorder( $contentObjectAttribute, $page, $zone['digit_id'], $zone['blockList'] );
        }
        
        // Deal with bloc with less or equals numbers of blocs 
        foreach ( $otherZones as $zone )
        {
            self::updateblockorder( $contentObjectAttribute, $page, $zone['digit_id'], $zone['blockList'] );
        }
        
        $contentObjectAttribute->setContent( $page );
        $contentObjectAttribute->store();
        
        eZContentCacheManager::clearContentCache( $contentObjectAttribute->attribute('contentobject_id') );
        
        return array();
    }
    
    public function getExistingBlocs( $page ) 
    {
        $zones = $page->attribute('zones');
        
        $existingBlocks = array();
        foreach ( $zones as $zone )
        {
            foreach ( $zone->attribute('blocks') as $block )
            {
                $existingBlocks[$zone->attribute('zone_identifier')][] = $block->attribute('id');
            }
        }
        
        return $existingBlocks;
    }
    
    /**
     * Update blocks order based on AJAX data send after D&D operation is finished
     * 
     * @param mixed $args
     * @return array
     */
    public static function updateblockorder( $contentObjectAttribute, $page, $zoneID, $blockOrder )
    {
        if ( $page )
            $zone = $page->getZone( $zoneID );
            
        if ( $zone )
            $zone->sortBlocks( $blockOrder );

        $contentObjectAttribute->setContent( $page );
        $contentObjectAttribute->store();

        return array();
    }
}

?>