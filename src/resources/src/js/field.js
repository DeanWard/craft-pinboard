import "../scss/field.scss";

import { createApp } from "vue";

import fieldApp from "../components/app.vue";

// This code listens for a custom event called "bootPinbaordField" 
// which is dispatched by pinBoardField.php.
window.addEventListener("bootPinbaordField", function (event) {
  bootPinbaordField("fields-" + event.detail);
});


//finds the element with the targetElementId and mounts the fieldApp component to it
function bootPinbaordField(targetElementId) {
  const element = document.getElementById(targetElementId);
  if (!element) {
    return;
  }

  const props = {
    id: element.dataset.id,
    name: element.dataset.name,
    namespacedId: element.dataset.namespacedid,
    url: element.dataset.url,
    field: JSON.parse(element.dataset.field),
    value: JSON.parse(element.dataset.value),
    pins: JSON.parse(element.dataset.pins),
  }

  console.log(props);
  const app = createApp(fieldApp, props).mount(element);
}
