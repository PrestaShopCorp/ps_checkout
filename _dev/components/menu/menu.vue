<template>
    <div id="addons-menu" class="bootstrap">
        <div class="page-head custom-tab">
            <div class="page-head-tabs" id="head_tabs">
                <ul class="nav">
                    <slot/>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Menu'
}
</script>

<style scoped lang="scss">
#content.bootstrap {
    padding-top: 160px;
}

#addons-menu {
    margin-bottom: 45px;
}

#addons-menu.bootstrap .page-head.custom-tab {
    height: auto !important;
    top: 8rem !important;
}

.mobile #addons-menu.bootstrap .page-head.custom-tab {
    top: 9rem !important;
}

#addons-menu.bootstrap .page-head .page-head-tabs ul {
    border-bottom: unset !important;
}

#addons-menu.bootstrap .page-head .page-head-tabs li {
    display: inline-block;
}

#content.bootstrap .panel-heading {
    margin: 0;
    padding: 12px 20px !important;
    padding: .75rem 1.25rem;
    background-color: #f5f5f5;
    font-family: Open Sans,Helvetica,Arial,sans-serif !important;
    font-weight: 700 !important;
    line-height: 1.2;
    color: #363a41 !important;
    height: 40px !important;
    text-transform: capitalize;
}

#content.bootstrap .panel-heading.color {
    color: #4a4a4a !important;
    text-align: center;
    background-color: #DDF4F8;
    height: 50px !important;
}
</style>
