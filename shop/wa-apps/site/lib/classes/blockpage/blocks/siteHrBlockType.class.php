<?php
/**
 * Horizontal ruler (hr)
 */
class siteHrBlockType extends siteBlockType
{
    public function getExampleBlockData()
    {
        $result = $this->getEmptyBlockData();
        $result->data = ['tag' => 'hr', 'block_props' => ['margin-top' => "m-t-20", 'margin-bottom' => "m-b-20", 'border-color' => [ 'css' => '#0000001a','name' => '1-1', 'type' => 'palette', 'value' => 'bd-tr-1' ]]];
        $result->data['inline_props'] = ['border-width' => ['value' => '3px', 'name' => 'Толщина 3', 'unit' => "px", 'type' => 'library']];
        return $result;
    }
    protected function getRawBlockSettingsFormConfig()
    {
        return [
            'type_name' => _w('Horizontal ruler'),
            'sections' => [
                [   'type' => 'BorderGroup',
                    'name' => _w('Border'),
                ],
                [   'type' => 'TabsWrapperGroup',
                    'name' => _w('Tabs'),
                ],
                [   'type' => 'MarginGroup',
                    'name' => _w('Margin'),
                ],
                [   'type' => 'ShadowsGroup',
                    'name' => _w('Shadows'),
                ],
                [   'type' => 'VisibilityGroup',
                    'name' => _w('Visibility on devices'),
                ],
                [   'type' => 'TagsGroup',
                    'name' => _w('SEO'),
                ],
            ],
        ] + parent::getRawBlockSettingsFormConfig();
    }
}
