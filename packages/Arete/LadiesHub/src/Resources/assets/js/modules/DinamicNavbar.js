/**
 * Calculates and sets the padding of an element according to the
 * height of another. Usually the padding of the body, acording to the
 * height of navbar.
 */
class calculatePadding {

    /**
     * Constructs the padding controller
     * @param {string} headSelector selector of the element to watch height
     * @param {string} paddedSelector selector of the element to add the padding
     */
    constructor(headSelector, paddedSelector = null) {
        
        this.paddedEl = paddedSelector ? document.querySelector(paddedSelector) : document;
        this.headSelector = headSelector;
        window.addEventListener('DOMContentLoaded', () => {
            this.setPadding();
        });
        window.addEventListener('resize', () => {
            // Updates after the rest of the UI has been updated
            window.setTimeout( () =>  this.setPadding() );
        })

    }

    setPadding() {
        let size = document.querySelector(this.headSelector).offsetHeight;
        document.body.style.paddingTop=size + 'px';
    }
}

/**
 * Hides/Shows navbar when scrolling down/up.
 */
class DinamicNavbar {
    /**
     * @param {string} navbar - Selector 
     * @param {Object} options
     * @param {number} options.disableWidth - Screen width in px <= at wich disable the styling of 
     *                 the dinamic navbar.
     */
    constructor(navbar, options) {
        this.elSelector = navbar;
        this.options = options;
        this.styleCache = new Map();
        this.initialized = false;
    }

    /** @returns HTMLELement */
    navEl() {
        return document.querySelector(this.elSelector);
    }

    /**
     *  Tries to init and return the initialization status
     * 
     * @returns {boolean}  false if not initialized */
    init() {
        if (this.initialized) {
            return true;
        }

        let el = this.navEl()
        if(el.style.display === 'none' || this.outOfWidth()) {
            return false; // init height can't be calc if not dispalyed normally.
        }

        this.initHeight = el.offsetHeight;
        this.height(this.initHeight);
        this.recordHistory;
        this.initialized = true;
        return true;
    }
    
    update() {
        if (this.disabled()) return;
        let calcHeight = this.limit(this.navEl().offsetHeight + this.getChange(1/3), this.initHeight, 0);
        this.height(calcHeight);
        this.recordHistory();
    }

    /**
     * Limit a number to a maximum and minumum value
     * 
     * @param {number} n the number to limit
     * @param {number} max the max it can be
     * @param {number} min the min it can be
     * @returns {number}
     */
    limit(n, max, min) {
        return  (n < min) ? min :
                (n > max) ? max : 
                n;
    }

    recordHistory() {
        this.lastPageYOffset = window.pageYOffset;
    }

    getChange(speed) {
        return (this.lastPageYOffset - window.pageYOffset) * speed;
    }

    /** @param {number} h height in px */
    height(h) {
        this.setStyle('height', h + 'px');
    }
    
    /**
     * Sets style of the element and caches it,
     * if it's not disabled.
     * 
     * @param {string} style
     * @param {string} value
     * @return {boolean} - false if is disabled, true otherwise.
     */
    setStyle(style, value) {
        this.styleCache.set(style, value)
        this.navEl().style[style] = value
        return true;
    }

    responsiveController(event) {
        if (this.disabled(event.target)) {
            // console.log('no permitido')
            this.clearStyles();
        }
        // } else {
        //     console.log('permitido')
        // }
    }

    clearStyles() {
        let el = this.navEl();
        this.styleCache.forEach((v, k) => el.style[k] = '');
    }

    /** 
     * @param {Window} w -the window object 
     * 
     * @returns {boolean}
     */
    disabled(w = null) {
        if (!this.init()) return true;
        w = w ? w : window;
        return this.outOfWidth(w);
    }

    /**
     * Reuturns if the el. is outh of enable width
     * 
     * @returns {boolean}
     */
    outOfWidth(w = null) {
        w = w ? w : window;
        return w.document.body.clientWidth <= this.options.disableWidth;
    }
    
    listen() {
        window.addEventListener('scroll', () => {
            this.update();
        })

        window.addEventListener('DOMContentLoaded', (event) => {
            this.init(); // tries to init
        })

        window.addEventListener('resize', (e) => {
            this.responsiveController(e);
        })
    }

}

export {DinamicNavbar as default, calculatePadding}