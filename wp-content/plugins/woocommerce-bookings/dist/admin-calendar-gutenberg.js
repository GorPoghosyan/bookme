/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};

;// CONCATENATED MODULE: external "React"
const external_React_namespaceObject = window["React"];
;// CONCATENATED MODULE: external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// CONCATENATED MODULE: external "lodash"
const external_lodash_namespaceObject = window["lodash"];
;// CONCATENATED MODULE: external ["wp","components"]
const external_wp_components_namespaceObject = window["wp"]["components"];
;// CONCATENATED MODULE: external "moment"
const external_moment_namespaceObject = window["moment"];
var external_moment_default = /*#__PURE__*/__webpack_require__.n(external_moment_namespaceObject);
;// CONCATENATED MODULE: ./src/js/components/datepicker-popover-month/index.js

/**
 * External dependencies
 */




/**
 * Internal dependencies
 */

class DatePickerPopoverMonth extends external_wp_element_namespaceObject.Component {
  constructor(props) {
    super(props);
    this.state = {
      showPicker: false
    };
    this.handleChange = this.handleChange.bind(this);
    const picker = this;
    document.addEventListener('click', function (event) {
      let element = document.getElementById('wc-bookings-datepicker-toggle-month');
      if (event.target === element || element.contains(event.target)) {
        return;
      }
      if (event.target.closest('.wc-bookings-datepicker-popover')) {
        return;
      }
      picker.setState({
        showPicker: false
      });
    });
  }
  handleChange(datetime) {
    const date = datetime.substring(0, 10);
    document.location.search += '&calendar_day=' + date + '&view=day';
  }
  toggleVisibility() {
    const {
      showPicker
    } = this.state;
    this.setState({
      showPicker: !showPicker
    });
  }
  render() {
    const {
      showPicker
    } = this.state;
    const {
      year,
      month
    } = this.props;
    return (0,external_React_namespaceObject.createElement)("div", null, (0,external_React_namespaceObject.createElement)("a", {
      href: "#",
      onClick: e => {
        e.preventDefault();
        this.toggleVisibility();
      },
      id: "wc-bookings-datepicker-toggle-month"
    }, month, " ", year, " ", (0,external_React_namespaceObject.createElement)("span", null, " \u25BE")), showPicker && (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Popover, {
      className: "wc-bookings-datepicker-popover"
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.DateTimePicker, {
      onChange: this.handleChange,
      currentDate: external_moment_default()('01 ' + month + ' ' + year, 'DD MMM YYYY').toDate(),
      __nextRemoveHelpButton: true,
      __nextRemoveResetButton: true
    })));
  }
}
/* harmony default export */ const datepicker_popover_month = (DatePickerPopoverMonth);
;// CONCATENATED MODULE: ./src/js/components/datepicker-popover-day/index.js

/**
 * External dependencies
 */




/**
 * Internal dependencies
 */

class DatePickerPopoverDay extends external_wp_element_namespaceObject.Component {
  constructor(props) {
    super(props);
    this.state = {
      showPicker: false
    };
    this.handleChange = this.handleChange.bind(this);
    const picker = this;
    document.addEventListener('click', function (event) {
      let element = document.getElementById('wc-bookings-datepicker-toggle-day');
      if (event.target === element || element.contains(event.target)) {
        return;
      }
      if (event.target.closest('.wc-bookings-datepicker-popover')) {
        return;
      }
      picker.setState({
        showPicker: false
      });
    });
  }
  handleChange(datetime) {
    const date = datetime.substring(0, 10);
    document.location.search += '&calendar_day=' + date + '&view=day';
  }
  toggleVisibility() {
    const {
      showPicker
    } = this.state;
    this.setState({
      showPicker: !showPicker
    });
  }
  render() {
    const {
      showPicker
    } = this.state;
    const {
      year,
      month,
      day
    } = this.props;
    return (0,external_React_namespaceObject.createElement)("div", null, (0,external_React_namespaceObject.createElement)("a", {
      href: "#",
      onClick: e => {
        e.preventDefault();
        this.toggleVisibility();
      },
      id: "wc-bookings-datepicker-toggle-day"
    }, day, " ", (0,external_React_namespaceObject.createElement)("span", null, " \u25BE")), showPicker && (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Popover, {
      className: "wc-bookings-datepicker-popover"
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.DateTimePicker, {
      onChange: this.handleChange,
      currentDate: external_moment_default()(Date.parse(day)).toDate(),
      __nextRemoveHelpButton: true,
      __nextRemoveResetButton: true
    })));
  }
}
/* harmony default export */ const datepicker_popover_day = (DatePickerPopoverDay);
;// CONCATENATED MODULE: external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// CONCATENATED MODULE: ./src/js/components/calendar-popover/index.js

/**
 * External dependencies
 */




/**
 * Internal dependencies
 */

class BookingDetails extends external_wp_element_namespaceObject.Component {
  render() {
    const {
      booking
    } = this.props;
    const {
      orderMeta
    } = booking;
    let parsedOrderMeta = [];
    if (orderMeta) {
      parsedOrderMeta = JSON.parse(orderMeta);
    }
    return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("div", null, (0,external_React_namespaceObject.createElement)("div", {
      id: "event_detail_header"
    }, (0,external_React_namespaceObject.createElement)("h3", null, booking.title)), (0,external_React_namespaceObject.createElement)("div", {
      id: "event_detail_body"
    }, (0,external_React_namespaceObject.createElement)("div", {
      id: "booking_status"
    }, booking.status && (0,external_React_namespaceObject.createElement)("span", null, booking.status)), (0,external_React_namespaceObject.createElement)("ul", null, booking.date && booking.time && (0,external_React_namespaceObject.createElement)("li", {
      className: "daily_popover_datetime_icon"
    }, (0,external_React_namespaceObject.createElement)("span", null, (0,external_React_namespaceObject.createElement)("strong", null, booking.date, (0,external_React_namespaceObject.createElement)("br", null), booking.time))), booking.customer && (0,external_React_namespaceObject.createElement)("li", {
      className: "daily_popover_customer_icon"
    }, (0,external_React_namespaceObject.createElement)("span", null, (0,external_React_namespaceObject.createElement)("strong", null, wc_bookings_admin_js_params.i18n_customer), booking.customer)), booking.resource && (0,external_React_namespaceObject.createElement)("li", {
      className: "daily_popover_resource_icon"
    }, (0,external_React_namespaceObject.createElement)("span", null, (0,external_React_namespaceObject.createElement)("strong", null, wc_bookings_admin_js_params.i18n_resource), booking.resource.split(',').join('<br />'))), booking.persons && (0,external_React_namespaceObject.createElement)("li", {
      className: "daily_popover_persons_icon"
    }, (0,external_React_namespaceObject.createElement)("span", null, (0,external_React_namespaceObject.createElement)("strong", null, wc_bookings_admin_js_params.i18n_persons), booking.persons)), parsedOrderMeta.map(({
      attrs,
      title
    }, index) => (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("li", {
      class: "daily_popover_group_title"
    }, (0,external_React_namespaceObject.createElement)("strong", null, title)), Object.keys(attrs).map(metaKey => (0,external_React_namespaceObject.createElement)("li", {
      className: "daily_popover_order_meta_item",
      dangerouslySetInnerHTML: {
        __html: `<strong>${metaKey}</strong>${attrs[metaKey]}`
      }
    })))))), booking.url && (0,external_React_namespaceObject.createElement)("div", {
      id: "event_detail_footer"
    }, (0,external_React_namespaceObject.createElement)("a", {
      href: booking.url
    }, wc_bookings_admin_js_params.i18n_view_details), " ", (0,external_React_namespaceObject.createElement)("span", null, "\u2192"))));
  }
}
class CalendarPopover extends external_wp_element_namespaceObject.Component {
  constructor(props) {
    super(props);
    this.state = {
      showPicker: false
    };
  }
  getBookingData() {
    const attrs = this.props.element.attributes;
    const mapping = {
      'data-id': 'id',
      'data-status': 'status',
      'data-booking-title': 'title',
      'data-booking-url': 'url',
      'data-booking-date': 'date',
      'data-booking-time': 'time',
      'data-booking-customer': 'customer',
      'data-booking-resource': 'resource',
      'data-booking-persons': 'persons',
      'data-order-meta': 'orderMeta'
    };
    let booking = {};
    Object.entries(mapping).forEach(([attrName, propName]) => {
      booking[propName] = attrs.getNamedItem(attrName) ? attrs.getNamedItem(attrName).value : '';
    });

    // Sanitize URL.
    booking['url'] = booking['url'] ? booking['url'].trim() : '';
    if (!/^https?:\/\/[^\s'"<>]+$/gi.test(booking['url'])) {
      booking['url'] = '';
    }
    return booking;
  }
  toggleVisibility = e => {
    e.preventDefault();
    const {
      showPicker
    } = this.state;
    this.setState({
      showPicker: !showPicker
    });
  };
  closePopover = () => {
    this.setState({
      showPicker: false
    });
  };
  parseStyles = styles => styles.split(';').filter(style => style.split(':')[0] && style.split(':')[1]).map(style => [style.split(':')[0].trim().replace(/-./g, c => c.substr(1).toUpperCase()), style.split(':')[1].trim()]).reduce((styleObj, style) => ({
    ...styleObj,
    [style[0]]: style[1]
  }), {});
  render() {
    const {
      showPicker
    } = this.state;
    const {
      getAnchorRect,
      position
    } = this.props;
    const attr = this.props.element.attributes;
    const classes = attr.getNamedItem('data-classes') ? attr.getNamedItem('data-classes').value : '';
    const styles = attr.getNamedItem('data-style') ? this.parseStyles(attr.getNamedItem('data-style').value) : {};
    const booking = this.getBookingData();
    return (0,external_React_namespaceObject.createElement)(external_wp_element_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)("a", {
      href: booking.url,
      className: classes,
      style: styles,
      onClick: this.toggleVisibility
    }, (0,external_React_namespaceObject.createElement)("span", {
      className: "booking-calendar-booking-title"
    }, booking.customer + (booking.customer ? ', ' : '') + booking.title), (0,external_React_namespaceObject.createElement)("span", {
      className: "booking-calendar-time-range"
    }, booking.time)), showPicker && (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Popover, {
      className: "wc-bookings-calendar-popover",
      focusOnMount: 'firstElement',
      getAnchorRect: getAnchorRect,
      position: position,
      onFocusOutside: this.closePopover
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Button, {
      className: "wc-bookings-calendar-popover-close",
      onClick: this.closePopover
    }, (0,external_wp_i18n_namespaceObject.__)('Close', 'woocommerce-bookings')), (0,external_React_namespaceObject.createElement)(BookingDetails, {
      booking: booking
    })));
  }
}
/* harmony default export */ const calendar_popover = (CalendarPopover);
;// CONCATENATED MODULE: ./src/js/admin-calendar-gutenberg.js

/* globals: wc_bookings_admin_calendar_js_params */

/**
 * External dependencies
 */



/**
 * Internal dependencies.
 */



const {
  default_month,
  default_year,
  default_day
} = wc_bookings_admin_calendar_js_params;
const getAnchorRect = popoverElement => popoverAnchorElement => {
  // set anchor tag if missing.
  if (!popoverAnchorElement || 'A' !== popoverAnchorElement.tagName) {
    popoverAnchorElement = popoverElement.querySelector('.wc-bookings-event-link');
  }
  const td = popoverElement.closest('td');
  const tableRect = popoverElement.closest('table').getBoundingClientRect();
  const content = popoverElement.querySelector('.components-popover__content') || popoverElement;
  const anchorRect = popoverAnchorElement.getBoundingClientRect();
  const anchorPoint = {
    x: anchorRect.x + td.clientWidth / 2,
    y: anchorRect.y
  };
  let x = anchorPoint.x;
  if (anchorPoint.x + content.clientWidth > tableRect.width + tableRect.x) {
    x = anchorPoint.x - content.clientWidth;
  }
  let y = anchorPoint.y;
  if (anchorPoint.y + content.clientHeight > document.body.clientHeight) {
    y = document.body.clientHeight - content.clientHeight;
  }
  return new DOMRect(x, y, 0, 0);
};
const popoverContainerMonth = document.getElementById('wc-bookings-datepicker-container-month');
if (null !== popoverContainerMonth) {
  const pickerMonth = (0,external_React_namespaceObject.createElement)(datepicker_popover_month, {
    year: default_year,
    month: default_month
  });
  (0,external_wp_element_namespaceObject.render)(pickerMonth, popoverContainerMonth);
}
const popoverContainerDay = document.getElementById('wc-bookings-datepicker-container-day');
if (null !== popoverContainerDay) {
  const pickerDay = (0,external_React_namespaceObject.createElement)(datepicker_popover_day, {
    year: default_year,
    month: default_month,
    day: default_day
  });
  (0,external_wp_element_namespaceObject.render)(pickerDay, popoverContainerDay);
}
const calendarEventMonth = document.getElementsByClassName('calendar_month_event');
if (null !== calendarEventMonth) {
  (0,external_lodash_namespaceObject.forEach)(calendarEventMonth, function (value) {
    (0,external_wp_element_namespaceObject.render)((0,external_React_namespaceObject.createElement)(calendar_popover, {
      getAnchorRect: getAnchorRect(value),
      element: value,
      position: 'bottom center'
    }), value);
  });
}
const calendarEventDay = document.getElementsByClassName('daily_view_booking');
if (null !== calendarEventDay) {
  (0,external_lodash_namespaceObject.forEach)(calendarEventDay, function (value) {
    (0,external_wp_element_namespaceObject.render)((0,external_React_namespaceObject.createElement)(calendar_popover, {
      element: value,
      position: 'bottom center'
    }), value.childNodes[0]);
  });
}
/******/ })()
;
//# sourceMappingURL=admin-calendar-gutenberg.js.map