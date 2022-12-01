@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
    $configData = Helper::appClasses();
@endphp

@isset($configData["layout"])
    @include((( $configData["layout"] === 'horizontal') ? 
        'content.adminside.layouts.horizontalLayout' :
        (( $configData["layout"] === 'blank') ? 
            'content.adminside.layouts.blankLayout' : 'layouts.contentNavbarLayout') ))
@endisset
