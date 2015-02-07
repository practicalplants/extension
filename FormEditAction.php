<?php
class PPFormEditAction extends SFFormEditAction{
    public function show( ){
        global $wgUser;
        if($wgUser->isAllowed( 'edit' )){
            self::displayForm($this, $this->page);
            return;
        }
        //user doesn't have permission, so we redirect
        //PracticalPlants_SSO_Auth::getInstance()->redirectToLogin($this->page);
        global $wgServer;
        
        header('Location: '.$wgServer.'/wiki/Special:UserLogin');
        exit;
    }   
}