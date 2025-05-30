<?php
/**
 * Single row. This is an element, but it may contain other elements.
 */
class siteRowBlockType extends siteBlockType
{

    public function getExampleBlockData()
    {
        // Default row contents: vertical sequence with heading and a paragraph of text
        $hseq = (new siteVerticalSequenceBlockType())->getEmptyBlockData();
        //$hseq->addChild((new siteHeadingBlockType())->getExampleBlockData());
        //$hseq->addChild((new siteParagraphBlockType())->getExampleBlockData());
        $hseq->data['is_horizontal'] = true;
        $hseq->data['is_complex'] = 'no_complex';
        $result = $this->getEmptyBlockData();
        $result->addChild($hseq, '');
        $result->data = ['block_props' => ['padding-top' => "p-t-10", 'padding-bottom' => "p-b-10"], 'wrapper_props' => ['justify-align' => "j-s"]];
      
        //$result->data['indestructible'] = true;
        return $result;
    }

    public function render(siteBlockData $data, bool $is_backend, array $tmpl_vars=[])
    {
        return parent::render($data, $is_backend, $tmpl_vars + [
            'children' => array_reduce($data->getRenderedChildren($is_backend), 'array_merge', []),
        ]);
    }

    protected function getRawBlockSettingsFormConfig()
    {
        return [
            'type_name' => _w('Row'),
            'sections' => [
                [   'type' => 'RowsAlignGroup',
                    'name' => _w('Alignment'),
                ],
                [   'type' => 'TabsWrapperGroup',
                    'name' => _w('Tabs'),
                ],
                [   'type' => 'PaddingGroup',
                    'name' => _w('Padding'),
                ],
                [   'type' => 'MarginGroup',
                    'name' => _w('Margin'),
                ],
                [   'type' => 'BorderGroup',
                    'name' => _w('Border'),
                    'is_block' => true, //Exception Row element
                ],
                [   'type' => 'BorderRadiusGroup',
                    'name' => _w('Angle'),
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
