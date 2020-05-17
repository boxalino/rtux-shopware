import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'narrative',
    label: 'Boxalino Narrative',
    component: 'sw-cms-el-narrative',
    configComponent: 'sw-cms-el-config-narrative',
    previewComponent: 'sw-cms-el-preview-narrative',
    defaultConfig: {
        widget: {
            source: 'static',
            value: null,
            required: true
        },
        sidebar: {
            source: 'static',
            value: false
        },
        hitCount: {
            source: 'static',
            value: 1,
            required: true
        },
        groupBy: {
            source: 'static',
            value: 'products_group_id',
            required: true
        },
        applyRequestParams: {
            source: 'static',
            type: Boolean,
            value: false
        },
        returnFields: {
            source: 'static',
            value: 'id,products_group_id'
        },
        filters: {
            source: 'static',
            value: null
        },
        categoryFilter: {
            source: 'static',
            value: 'navigation',
            required: true
        },
        categoryFilterList: {
            source: 'static',
            value: null
        },
        facets: {
            source: 'static',
            value: null
        }
    }
});
