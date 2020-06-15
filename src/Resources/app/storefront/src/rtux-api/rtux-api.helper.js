import CookieStorageHelper from 'src/helper/storage/cookie-storage.helper';

/**
 * Helper class assisting in making requests to Boxalino API from front-end
 */
export default class RtuxApiHelper {

    /**
     * additional parameters to be set: returnFields, filters, facets, sort
     * for more details, check the Narrative Api Technical Integration manual provided by Boxalino
     *
     * @param string account
     * @param string apiKey
     * @param string widget
     * @param string language
     * @param string groupBy
     * @param int hitCount
     * @param bool dev
     * @param bool test
     * @param {*} otherParameters
     * @returns {{widget: *, hitCount: number, apiKey: *, dev: boolean, test: boolean, profileId: (string|*|{}|DOMPoint|SVGTransform|SVGNumber|SVGLength|SVGPathSeg), customerId: (string|*), language: *, sessionId: *, groupBy: *, parameters: {"User-Agent": string, "User-URL", "User-Referer": string}, username: *}}
     */
    getApiRequestData(account, apiKey, widget, language, groupBy, hitCount = 1, dev=false, test=false, customerId = null, otherParameters={}) {
        var baseParameters = {
            'username':account,
            'apiKey': apiKey,
            'sessionId':this.getApiSessionId(),
            'profileId':this.getApiProfileId(),
            'customerId':this.getApiCustomerId(customerId),
            'widget': widget,
            'dev': dev,
            'test': test,
            'hitCount':hitCount,
            'language': language,
            'groupBy': groupBy,
            'parameters': {
                'User-Referer':document.referrer,
                'User-URL':window.location.href,
                'User-Agent':navigator.userAgent
            }
        };

        return Object.assign({}, baseParameters, otherParameters);
    }

    /**
     * @public
     * @param url
     * @returns {string}
     */
    getApiRequestUrl(url) {
        return url + '?profileId=' + encodeURIComponent(this._getApiProfileId());
    }

    /**
     * @public
     * @param string|null customerId
     * @returns {string|*}
     */
    getApiCustomerId(customerId=null) {
        if(customerId) {
            return atob(customerId);
        }

        return this._getApiProfileId();
    }

    /**
     * @public
     * @returns {string|*|{}|DOMPoint|SVGTransform|SVGNumber|SVGLength|SVGPathSeg}
     */
    getApiProfileId() {
        return CookieStorageHelper.getItem('cemv');
    }

    /**
     * @public
     * @returns {string|*|{}|DOMPoint|SVGTransform|SVGNumber|SVGLength|SVGPathSeg}
     */
    getApiSessionId() {
        return CookieStorageHelper.getItem('cems');
    }

}
