<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   $companySettings = $db->get('company_settings'); ?>
<website.routes></website.routes>
<div class="{window.loggedInUser$ && loggedInUser$.virtual_user ? 'fullscreen-app' : ''} header-bar container-{router$.params?.page}">
    <div class="logo" >
        <img link="/" src="/uploads/logos/<?= $companySettings['logo'] ?>">
    </div>
    
    <div class="menu" for="menuItem of this.menu">
        <a class="{this.currentPage?.id == menuItem.id ? 'active' : ''}" (click)="this.gotoSubmenu(menuItem)">{menuItem.title_translated} </a>
    </div>
 


   <menu></menu>

    <div class="app page-{router$.params?.page} {!window?.loggedInUser$ || !loggedInUser$.username ? 'logged-out' : 'logged-in'}">
        <main if="window.router$ != undefined" class="cards" element="mainEl">
            <include tag="{router$ && router$.active && router$.active.content ? router$.active.content.tag : null}"
                attributes="{router$.active then router$.attributes}"></include>
            <legal-overview></legal-overview>  
        </main>
    </div>
</div>

<when test="window.router$ && router$ && router$.active && router$.active.content && router$.params" then="App.pushBreadcrumbs(true)"></when>
<toast element="toaster"></toast>
<timezone-handler></timezone-handler>