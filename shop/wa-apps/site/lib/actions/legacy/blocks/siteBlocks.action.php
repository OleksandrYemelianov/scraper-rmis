<?php

class siteBlocksAction extends waViewAction
{
    public function execute()
    {
        $id = waRequest::get('id');
        $model = new siteBlockModel();
        $blocks = $model->order('sort')->fetchAll('id');

        $apps = wa()->getApps();
        foreach ($apps as $app_id => $app) {
            $site_config = $this->getAppSiteConfig($app_id);
            if (!empty($site_config['blocks'])) {
                foreach ($site_config['blocks'] as $block_id => $block) {
                    if (!is_array($block)) {
                        $block = array('content' => $block, 'description' => '');
                    }
                    $block_id = $app_id.'.'.$block_id;
                    if (!isset($blocks[$block_id])) {
                        $block['id'] = $block_id;
                        $block['app'] = $app;
                        $blocks[$block_id] = $block;
                    } else {
                        if ($block_id == $id) {
                            $blocks[$block_id]['original'] = trim($block['content']);
                        }
                    }
                }
            }
        }

        foreach ($blocks as $block_id => $block) {
            if (empty($block['app'])) {
                if (($pos = strpos($block_id, '.')) !== false) {
                    $app_id = substr($block_id, 0, $pos);
                    if (isset($apps[$app_id])) {
                        $blocks[$block_id]['app_icon'] = $apps[$app_id]['icon'];
                    }
                }
            }
        }

        if (!isset($id)) {
            $id = key($blocks);
        }
        $this->view->assign('blocks', $blocks);
        if ($id && isset($blocks[$id])) {
            $block = $blocks[$id];
        } else {
            $block = null;
        }
        $this->view->assign('block', $block);
        $this->view->assign('editor', true);
        $this->view->assign('is_new_block', !$id);

        $this->view->assign('domain_id', siteHelper::getDomainId());
    }

    private function getAppSiteConfig($app_id)
    {
        $site_config = array();
        $path = $this->getConfig()->getAppsPath($app_id, 'lib/config/site.php');
        if (file_exists($path)) {
            wa($app_id);
            $site_config = include($path);
            if (!is_array($site_config)) {
                $site_config = array();
            }
        }
        return $site_config;
    }
}
