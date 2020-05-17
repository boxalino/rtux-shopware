import template from './sw-cms-el-narrative.html.twig';
import './sw-cms-el-narrative.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-narrative', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('narrative');
        }
    }
});
