(this.webpackJsonp = this.webpackJsonp || []).push([
    ["boxalino-real-time-user-experience"], {
        "+Yo5": function(e, n, t) {
            "use strict";
            t.r(n);
            var i = t("Y7O/"),
                o = t.n(i);
            t("R+ZI");
            const {
                Component: a
            } = Shopware;
            a.register("sw-cms-block-narrative", {
                template: o.a
            });
            var l = t("6MZ8"),
                r = t.n(l);
            t("Rc+P");
            const {
                Component: c
            } = Shopware;
            c.register("sw-cms-preview-narrative", {
                template: r.a
            }), Shopware.Service("cmsService").registerCmsBlock({
                name: "narrative",
                label: "Boxalino Narrative",
                category: "commerce",
                hidden: !1,
                removable: !1,
                component: "sw-cms-block-narrative",
                previewComponent: "sw-cms-preview-narrative",
                defaultConfig: {
                    marginBottom: "20px",
                    marginTop: "20px",
                    marginLeft: "20px",
                    marginRight: "20px",
                    sizingMode: "boxed"
                },
                slots: {
                    narrative: {
                        type: "narrative",
                        default: {
                            config: {
                                groupBy: {
                                    value: "products_group_id"
                                },
                                categoryFilter: {
                                    value: "navigation"
                                }
                            }
                        }
                    }
                }
            });
            var s = t("IRRf"),
                m = t.n(s);
            t("XWFO");
            const {
                Component: d,
                Mixin: p
            } = Shopware;
            d.register("sw-cms-el-narrative", {
                template: m.a,
                mixins: [p.getByName("cms-element")],
                created() {
                    this.createdComponent()
                },
                methods: {
                    createdComponent() {
                        this.initElementConfig("narrative")
                    }
                }
            });
            var v = t("xoib"),
                u = t.n(v);
            t("ZTms");
            const {
                Component: f,
                Mixin: g
            } = Shopware;
            f.register("sw-cms-el-config-narrative", {
                template: u.a,
                mixins: [g.getByName("cms-element")],
                created() {
                    this.createdComponent()
                },
                methods: {
                    createdComponent() {
                        this.initElementConfig("narrative")
                    }
                }
            });
            var w = t("Dm71"),
                b = t.n(w);
            t("W54G");
            Shopware.Component.register("sw-cms-el-preview-narrative", {
                template: b.a
            }), Shopware.Service("cmsService").registerCmsElement({
                name: "narrative",
                label: "Boxalino Narrative",
                component: "sw-cms-el-narrative",
                configComponent: "sw-cms-el-config-narrative",
                previewComponent: "sw-cms-el-preview-narrative",
                defaultConfig: {
                    widget: {
                        source: "static",
                        value: null,
                        required: !0
                    },
                    sidebar: {
                        source: "static",
                        value: false
                    },
                    hitCount: {
                        source: "static",
                        value: 1,
                        required: !0
                    },
                    groupBy: {
                        source: "static",
                        value: "products_group_id",
                        required: !0
                    },
                    applyRequestParams: {
                        source: "static",
                        type: Boolean,
                        value: false
                    },
                    returnFields: {
                        source: "static",
                        value: "id,products_group_id"
                    },
                    filters: {
                        source: "static",
                        value: null
                    },
                    categoryFilter: {
                        source: "static",
                        value: "navigation",
                        required: !0
                    },
                    categoryFilterList: {
                        source: "static",
                        value: null
                    },
                    facets: {
                        source: "static",
                        value: null
                    }
                }
            })
        },
        "/rWM": function(e, n, t) {},
        "6MZ8": function(e, n) {
            e.exports = '{% block sw_cms_block_narrative_preview %}\n    <div class="sw-cms-preview-narrative">\n        <p><b>Boxalino API Narrative</b></p>\n        <p>1. Create request definition service</p>\n        <p>2. Create content loader handler</p>\n        <p>3. Configure in Boxalino Intelligence</p>\n        <p>4. Re-use.</p>\n    </div>\n{% endblock %}\n'
        },
        "7/dH": function(e, n, t) {},
        Dm71: function(e, n) {
            e.exports = '{% block sw_cms_el_preview_narrative %}\n    <div class="sw-cms-el-preview-narrative">\n        <p>Boxalino Narrative Element</p>\n    </div>\n{% endblock %}\n'
        },
        IRRf: function(e, n) {
            e.exports = '{% block sw_cms_element_narrative %}\n    <div class="sw-cms-el-narrative">\n        <p><b>Boxalino Narrative Element</b></p>\n        <ul>\n            <li><i>Recommendations Slider</i> (products, blogs, media, etc)</li>\n            <li><i>Category Layout per narrative configuration</i> (page title, facets, product listing, other CMS elements, etc)</li>\n            <li><i>CMS elements</i> (as configured in your narrative)</li>\n        </ul>\n    </div>\n{% endblock %}\n'
        },
        "R+ZI": function(e, n, t) {
            var i = t("/rWM");
            "string" == typeof i && (i = [
                [e.i, i, ""]
            ]), i.locals && (e.exports = i.locals);
            (0, t("SZ7m").default)("3e749687", i, !0, {})
        },
        "Rc+P": function(e, n, t) {
            var i = t("Z71M");
            "string" == typeof i && (i = [
                [e.i, i, ""]
            ]), i.locals && (e.exports = i.locals);
            (0, t("SZ7m").default)("dab0c6c8", i, !0, {})
        },
        W54G: function(e, n, t) {
            var i = t("7/dH");
            "string" == typeof i && (i = [
                [e.i, i, ""]
            ]), i.locals && (e.exports = i.locals);
            (0, t("SZ7m").default)("32cb4f66", i, !0, {})
        },
        XWFO: function(e, n, t) {
            var i = t("csMY");
            "string" == typeof i && (i = [
                [e.i, i, ""]
            ]), i.locals && (e.exports = i.locals);
            (0, t("SZ7m").default)("6b7ee407", i, !0, {})
        },
        "Y7O/": function(e, n) {
            e.exports = '{% block sw_cms_block_narrative %}\n    <div class="sw-cms-block-narrative">\n        <slot name="narrative"></slot>\n    </div>\n{% endblock %}\n'
        },
        YqJw: function(e, n, t) {},
        Z71M: function(e, n, t) {},
        ZTms: function(e, n, t) {
            var i = t("YqJw");
            "string" == typeof i && (i = [
                [e.i, i, ""]
            ]), i.locals && (e.exports = i.locals);
            (0, t("SZ7m").default)("d876e77c", i, !0, {})
        },
        csMY: function(e, n, t) {},
        xoib: function(e, n) {
            e.exports = '{% block sw_cms_element_narrative_config %}\r\n    <div class=\"sw-cms-el-config-narrative\">\r\n        {% block sw_cms_element_narrative_config_widget %}\r\n            <sw-field v-model=\"element.config.widget.value\"\r\n                      type=\"text\"\r\n                      label=\"Widget*\"\r\n                      placeholder=\"Boxalino Widget\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_sidebar %}\r\n            <sw-field type=\"switch\"\r\n                      bordered\r\n                      label=\"Sidebar Layout\"\r\n                      v-model=\"element.config.sidebar.value\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_hitCount %}\r\n            <sw-field  type=\"text\" v-model=\"element.config.hitCount.value\"\r\n                       label=\"Nr of returned products*\"\r\n                       placeholder=\"Hit Count Value\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_groupBy %}\r\n            <sw-field type=\"text\" v-model=\"element.config.groupBy.value\"\r\n                      label=\"Group by (field name)*\"\r\n                      placeholder=\"products_group_id\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_applyRequestParams %}\r\n            <sw-boolean-radio-group v-model=\"element.config.applyRequestParams.value\"\r\n                                    label=\"Apply Request Parameters\"\r\n                                    labelOptionTrue=\"Yes, content updates on customer facet\/sorting\/filter select\"\r\n                                    labelOptionFalse=\"No, content is independent of customer actions\"\r\n                                    bordered>\r\n            <\/sw-boolean-radio-group>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_returnFields %}\r\n            <sw-field type=\"text\" v-model=\"element.config.returnFields.value\"\r\n                      label=\"Returned Fields (divided by comma (,))\"\r\n                      placeholder=\"id, products_title, discountedPrice, products_image, ..\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_filters %}\r\n            <sw-field type=\"text\" v-model=\"element.config.filters.value\"\r\n                      label=\"Filters (list, divided by comma (,))\"\r\n                      placeholder=\"products_visibility=20\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_categoryFilter %}\r\n            <sw-select-field placeholder=\"Category Filter Scope\"\r\n                             v-model=\"element.config.categoryFilter.value\"\r\n                             label=\"Category Filter\">\r\n                <option value=\"root\">Sales Channel Navigation Category<\/option>\r\n                <option value=\"navigation\">Current Category<\/option>\r\n                <option value=\"custom\">Custom category IDs<\/option>\r\n            <\/sw-select-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_categoryFilterList %}\r\n            <sw-field type=\"text\" v-model=\"element.config.categoryFilterList.value\"\r\n                      label=\"Category List for Filters (divided by comma(,))\"\r\n                      placeholder=\"categoryId1,categoryId2\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n\r\n        {% block sw_cms_element_narrative_config_facets %}\r\n            <sw-field type=\"text\" v-model=\"element.config.facets.value\"\r\n                      label=\"Facets (list, divided by comma (,))\">\r\n            <\/sw-field>\r\n        {% endblock %}\r\n    <\/div>\r\n{% endblock %}\r\n'
        }
    },
    [
        ["+Yo5", "runtime", "vendors-node"]
    ]
]);
