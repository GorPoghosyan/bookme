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
;// CONCATENATED MODULE: external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// CONCATENATED MODULE: external "moment"
const external_moment_namespaceObject = window["moment"];
var external_moment_default = /*#__PURE__*/__webpack_require__.n(external_moment_namespaceObject);
;// CONCATENATED MODULE: ./src/js/components/store-availability-popover/index.js

/**
 * External dependencies
 */





/**
 * Internal dependencies
 */

const AVAILABILITY_ALL_DAY = '1';
const AVAILABILITY_SPECIFIC_HOURS = '2';
const DOES_NOT_REPEAT = '1';
const REPEATS_WEEKLY = '2';
const REPEATS_YEARLY = '3';
const REPEATS_SPECIFIC_DAYS = '4';
const REPEATS_SPECIFIC_DATES = '5';
function StoreAvailabilityPopover(props) {
  const [isPopoverVisible, setPopoverVisible] = (0,external_wp_element_namespaceObject.useState)(true);
  const [startDate, setStartDate] = (0,external_wp_element_namespaceObject.useState)(props.selectedDate || new Date());
  const [needsEndDate, setNeedsEndDate] = (0,external_wp_element_namespaceObject.useState)(false);
  const [endDate, setEndDate] = (0,external_wp_element_namespaceObject.useState)(props.selectedDate || new Date());
  const [isDatePickerVisible, setDatePickerVisible] = (0,external_wp_element_namespaceObject.useState)(false);
  const [availabilityTime, setAvailabilityTime] = (0,external_wp_element_namespaceObject.useState)(AVAILABILITY_ALL_DAY);
  const [availabilityTimeFrames, setAvailabilityTimeFrames] = (0,external_wp_element_namespaceObject.useState)([]);
  const [frequency, setFrequency] = (0,external_wp_element_namespaceObject.useState)(DOES_NOT_REPEAT);
  const [repeatDaysOfWeek, setRepeatDaysOfWeek] = (0,external_wp_element_namespaceObject.useState)();
  const selectedDayOfWeek = external_moment_default()(props.selectedDate).format('dddd');
  const selectedDay = external_moment_default()(props.selectedDate).format('MMMM D');
  function popoverContent() {
    return (0,external_React_namespaceObject.createElement)("div", {
      className: "wb__sap"
    }, (0,external_React_namespaceObject.createElement)("h4", {
      className: "wb__sap__heading"
    }, (0,external_wp_i18n_namespaceObject.__)('Edit Availability', 'woocommerce-bookings')), (0,external_React_namespaceObject.createElement)("div", {
      className: "wb__sap__start-end-dates wb__sap__row"
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.TextControl, {
      className: "wb__sap__start-date",
      label: (0,external_wp_i18n_namespaceObject.__)('Start Date', 'woocommerce-bookings'),
      onClick: selectStartDate,
      value: formatDate(startDate)
    }), !needsEndDate ? (0,external_React_namespaceObject.createElement)("span", {
      className: "wb__sap__add-end-date",
      onClick: selectEndDate
    }, (0,external_wp_i18n_namespaceObject.__)('Add end date', 'woocommerce-bookings')) : (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.TextControl, {
      className: "wb__sap__end-date",
      label: (0,external_wp_i18n_namespaceObject.__)('End Date', 'woocommerce-bookings'),
      onClick: selectEndDate,
      value: formatDate(endDate)
    })), (0,external_React_namespaceObject.createElement)("div", {
      className: "wb__sap__time wb__sap__row"
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.SelectControl, {
      label: (0,external_wp_i18n_namespaceObject.__)('Time', 'woocommerce-bookings'),
      className: "wb__sap__time-selector",
      value: availabilityTime,
      onChange: changeAvailabilityTime,
      options: [{
        label: (0,external_wp_i18n_namespaceObject.__)('Available all day', 'woocommerce-bookings'),
        value: AVAILABILITY_ALL_DAY
      }, {
        label: (0,external_wp_i18n_namespaceObject.__)('Available at specific hours', 'woocommerce-bookings'),
        value: AVAILABILITY_SPECIFIC_HOURS
      }]
    }), isAvailableAtSpecificHours() && renderTimeFrames(availabilityTimeFrames, setAvailabilityTimeFrames)), (0,external_React_namespaceObject.createElement)("div", {
      className: "wb__sap__frequency wb__sap__row"
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.SelectControl, {
      label: (0,external_wp_i18n_namespaceObject.__)('Frequency', 'woocommerce-bookings'),
      className: "wb__sap__frequency-selector",
      value: frequency,
      onChange: changeFrequency,
      options: [{
        label: (0,external_wp_i18n_namespaceObject.__)('Does not repeat', 'woocommerce-bookings'),
        value: DOES_NOT_REPEAT
      }, /* translators: %s: day of week */
      {
        label: (0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Every %s', 'woocommerce-bookings'), selectedDayOfWeek),
        value: REPEATS_WEEKLY
      }, /* translators: %s: day of year */
      {
        label: (0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Anually on %s', 'woocommerce-bookings'), selectedDay),
        value: REPEATS_YEARLY
      }, {
        label: (0,external_wp_i18n_namespaceObject.__)('On specific days of the week', 'woocommerce-bookings'),
        value: REPEATS_SPECIFIC_DAYS
      }, {
        label: (0,external_wp_i18n_namespaceObject.__)('On specific dates', 'woocommerce-bookings'),
        value: REPEATS_SPECIFIC_DATES
      }]
    }), doesRepeatSpecificDaysOfWeek() && (0,external_React_namespaceObject.createElement)("div", {
      className: "wb__sap__frequency-days-of-week wb__sap__row"
    }, (0,external_React_namespaceObject.createElement)("span", null, (0,external_wp_i18n_namespaceObject.__)('Repeats on:', 'woocommerce-bookings')), (0,external_React_namespaceObject.createElement)("ul", {
      className: "wb__sap__days-of-week-selector"
    }, daysOfWeekSelector(changeRepeatDayOfWeek, repeatDaysOfWeek)))), (0,external_React_namespaceObject.createElement)("div", {
      className: "wb__sap__actions wb__sap__row"
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Button, {
      className: "wb__sap__cancel",
      isTertiary: true,
      isLink: true,
      onClick: closePopover
    }, (0,external_wp_i18n_namespaceObject.__)('Cancel', 'woocommerce-bookings')), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Button, {
      className: "wb__sap__apply",
      isDefault: true,
      isPrimary: true
    }, (0,external_wp_i18n_namespaceObject.__)('Apply', 'woocommerce-bookings'))));
  }
  function datePicker() {
    return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Popover, {
      className: "wb__sap_date-picker",
      position: "middle center",
      onClickOutside: () => setDatePickerVisible(false),
      focusOnMount: false
    }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.DatePicker, {
      currentDate: startDate,
      onChange: date => {
        setDatePickerVisible(false);
        updateDate(date);
      }
    }));
  }
  function selectStartDate() {
    setDatePickerVisible(!isDatePickerVisible);
    updateDate = setStartDate;
  }
  function selectEndDate() {
    setNeedsEndDate(true);
    setDatePickerVisible(!isDatePickerVisible);
    updateDate = setEndDate;
  }
  function changeAvailabilityTime(newAvailabilityTime) {
    setAvailabilityTime(newAvailabilityTime);
    if (availabilityTimeFrames.length === 0) {
      availabilityTimeFrames.push(['0', '0']);
      setAvailabilityTimeFrames(availabilityTimeFrames);
    }
  }
  function isAvailableAtSpecificHours() {
    return availabilityTime === AVAILABILITY_SPECIFIC_HOURS;
  }
  function changeFrequency(newFrequency) {
    setFrequency(newFrequency);
  }
  function doesRepeatSpecificDaysOfWeek() {
    return frequency === REPEATS_SPECIFIC_DAYS;
  }
  function changeRepeatDayOfWeek(event) {
    const dayOfWeek = parseInt(event.target.value);
    let selectedDays = repeatDaysOfWeek || [];
    if (!selectedDays.includes(dayOfWeek)) {
      selectedDays.push(dayOfWeek);
    } else {
      selectedDays = selectedDays.filter(day => day !== dayOfWeek);
    }
    setRepeatDaysOfWeek(selectedDays.slice());
  }
  function closePopover() {
    if (!isDatePickerVisible) {
      setPopoverVisible(false);
    }
  }
  return isPopoverVisible && (0,external_React_namespaceObject.createElement)("div", {
    className: "wb__sap__container"
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Popover, {
    position: "middle center",
    onClickOutside: closePopover,
    focusOnMount: false
  }, popoverContent()), isDatePickerVisible && datePicker());
}
function formatDate(date) {
  return external_moment_default()(date).format('MMM DD, YYYY');
}
function renderTimeFrames(availabilityTimeFrames, setAvailabilityTimeFrames) {
  const timeFrames = [];
  function changeTimeFrame(timeFrameIndex, comboIndex) {
    return function (newTime) {
      availabilityTimeFrames[timeFrameIndex][comboIndex] = newTime;
      setAvailabilityTimeFrames(availabilityTimeFrames.slice());
    };
  }
  function addAnotherTimeFrame() {
    availabilityTimeFrames.push(['0', '0']);
    setAvailabilityTimeFrames(availabilityTimeFrames.slice());
  }
  for (var timeFrameIndex = 0; timeFrameIndex < availabilityTimeFrames.length; timeFrameIndex++) {
    const timeFrame = (0,external_React_namespaceObject.createElement)("div", {
      key: timeFrameIndex,
      className: "wb__sap__time-frame wb__sap__row"
    }, hoursOfDayCombo(availabilityTimeFrames[timeFrameIndex][0], changeTimeFrame(timeFrameIndex, 0), timeFrameIndex, 0), (0,external_React_namespaceObject.createElement)("span", null, "\u2014"), hoursOfDayCombo(availabilityTimeFrames[timeFrameIndex][1], changeTimeFrame(timeFrameIndex, 1), timeFrameIndex, 1));
    timeFrames.push(timeFrame);
  }
  timeFrames.push((0,external_React_namespaceObject.createElement)("div", {
    key: "add-more-times",
    className: "wb__sap__add-new-time-frame",
    onClick: addAnotherTimeFrame
  }, (0,external_wp_i18n_namespaceObject.__)('Add more times', 'woocommerce-bookings')));
  return timeFrames;
}
function hoursOfDayCombo(selectedTime, updateSelectedTime, timeFrameIndex, selectIndex) {
  const options = [];
  for (var hour = 0; hour < 24; hour++) {
    options.push({
      label: external_moment_default()().startOf('day').add(hour, 'hour').format('h:mma'),
      value: hour
    });
  }
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.SelectControl, {
    key: `${timeFrameIndex}-${selectIndex}`,
    value: selectedTime,
    onChange: updateSelectedTime,
    className: "wb__sap__time-frame-hours-selector",
    options: options
  });
}
function daysOfWeekSelector(changeRepeatDayOfWeek, repeatDaysOfWeek) {
  const daysOfWeek = [];
  for (var dayIndex = 0; dayIndex < 7; dayIndex++) {
    const day = external_moment_default()().startOf('week').add(dayIndex, 'days');
    const dayOfWeek = day.weekday();
    const dayName = day.format('dd');
    const selected = (repeatDaysOfWeek || []).includes(dayOfWeek);
    const classNames = ['wb__sap__day-of-week-button'];
    if (selected) {
      classNames.push('selected');
    }
    daysOfWeek.push((0,external_React_namespaceObject.createElement)("li", {
      key: dayIndex
    }, (0,external_React_namespaceObject.createElement)("button", {
      className: classNames.join(' '),
      onClick: changeRepeatDayOfWeek,
      value: dayOfWeek,
      "data-day": dayName
    }, dayName)));
  }
  return daysOfWeek;
}
/* harmony default export */ const store_availability_popover = (StoreAvailabilityPopover);
;// CONCATENATED MODULE: ./src/js/admin-store-availability.js

/* globals: wc_bookings_admin_calendar_js_params */

/**
 * External dependencies
 */



/**
 * Internal dependencies.
 */

let popoverContainer;
const storeAvailabilityDays = document.getElementsByClassName('wc-bookings__store-availability-day');
if (null !== storeAvailabilityDays) {
  (0,external_lodash_namespaceObject.forEach)(storeAvailabilityDays, storeAvailabilityDay => {
    storeAvailabilityDay.addEventListener('click', () => {
      const date = new Date(parseInt(storeAvailabilityDay.dataset.timestamp) * 1000 /* to milliseconds */);
      if (popoverContainer) {
        document.body.removeChild(popoverContainer);
      }
      popoverContainer = document.createElement('div');
      document.body.appendChild(popoverContainer);
      (0,external_wp_element_namespaceObject.render)((0,external_React_namespaceObject.createElement)(store_availability_popover, {
        selectedDate: date
      }), popoverContainer);
    });
  });
}
/******/ })()
;
//# sourceMappingURL=admin-store-availability.js.map