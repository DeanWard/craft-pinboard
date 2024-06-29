<script setup>
import { onMounted, ref, computed } from "vue";
import draggable from "vuedraggable";

const labelLengthLimit = 30;
const canvasImage = ref(null);
const canvas = ref(null);
const draggedPin = ref(null);
const pinToolTip = ref(null);
const canvasURL = ref("");
const workingPins = ref([]);
const saveData = ref([]);
const mouseX = ref(null);
const mouseY = ref(null);

//define props
const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  id: {
    type: String,
    required: true,
  },
  namespacedId: {
    type: String,
    required: true,
  },
  field: {
    type: Object,
    required: true,
  },
  value: {
    type: Object,
    required: true,
  },
  url: {
    type: String,
    required: true,
  },
  pins: {
    type: Array,
    required: true,
  },
});



onMounted(() => {
  canvasImage.value.onload = function () {
    canvas.value.style.width = `${canvasImage.value.width}px`;
    canvas.value.style.height = `${canvasImage.value.height}px`;
  };

  loadSavedPins();

  if (props.url) {
    canvasURL.value = props.url;
    console.log("setting canvasURL to", canvasURL.value);
  }

  //subscribe to backdropSelect picker
  subscribeToPicker("backdropSelect", function (e) {
    let url = e.elements[0].url || "";
    if (url.length > 0) {
      canvasURL.value = url;
    }
  });

  //subscribe to entrySelect picker
  subscribeToPicker(
    "entrySelect",
    function (e) {
      const pins = mapPins(e.elements, "entries");
      pins.forEach((pin) => {
        pushPin(pin);
      });
    },
    function (e) {
      resyncPins(e, "entries");
    }
  );

  //subscribe to userSelect picker
  subscribeToPicker(
    "userSelect",
    function (e) {
      const pins = mapPins(e.elements, "users");
      pins.forEach((pin) => {
        pushPin(pin);
      });
    },
    function (e) {
      resyncPins(e, "users");
    }
  );

  //subscribe to categorySelect picker
  subscribeToPicker(
    "categorySelect",
    function (e) {
      const pins = mapPins(e.elements, "categories");
      pins.forEach((pin) => {
        pushPin(pin);
      });
    },
    function (e) {
      resyncPins(e, "categories");
    }
  );
});

const loadSavedPins = () => {
  let vueData = props.value.vuedata;
  let pinLocations;
  
  if (vueData) {
    pinLocations = JSON.parse(vueData);
  }

  for (const [type, pins] of Object.entries(props.pins)) {
    const mappedPins = mapPins(pins, type);
    mappedPins.forEach((pin) => {
      pushPin(pin);
    });
  }

  workingPins.value = workingPins.value.map((pin) => {
    if (pinLocations) {
      const savedPin = pinLocations.find((p) => p.id === pin.id);
      if (savedPin) {
        pin.x = savedPin.x;
        pin.y = savedPin.y;
      }
    }
    return pin;
  });
};

const mapPins = (pins, type) => {
  return pins.map((pin) => {
    return {
      id: pin.id,
      label: pin.label ?? pin.title ?? pin._friendlyName ?? pin.name,
      url: pin.url,
      x: pin.x ?? null,
      y: pin.y ?? null,
      type: type,
    };
  });
};

const resyncPins = (e, type) => {
  const remainingPins = [];

  // Populate remainingPins with the id and label of each item
  for (const item of e.target.elementSelect.$items) {
    remainingPins.push({
      id: item.dataset.id,
      label: item.dataset.label,
    });
  }

  // Filter out any pins whose type matches 'type' and whose id is not in remainingPins
  workingPins.value = workingPins.value.filter((pin) => {
    return pin.type !== type || remainingPins.some((p) => p.id == pin.id);
  });
};

const pushPin = (pin) => {
  //check we don't already have a pin with this id
  if (workingPins.value.find((p) => p.id === pin.id)) {
    return;
  }
  workingPins.value.push(pin);
};

const subscribeToPicker = (elementType, onSelect = null, onRemove = null) => {
  let subID = `${props.namespacedId}-${elementType}`;
  $(document).ready(function () {
    let element = $(`#${subID}`);

    if (!element.length) {
      console.log("element not found", subID);
      return;
    }

    if (onSelect !== null) {
      element.data("elementSelect").on("selectElements", function (e) {
        onSelect(e);
      });
    }
    if (onRemove !== null) {
      element.data("elementSelect").on("removeElements", function (e) {
        onRemove(e);
      });
    }
  });
};

const pinData = computed(() => {
  return JSON.stringify(saveData.value);
});

const pinsWithCoords = computed(() => {
  return workingPins.value.filter((pin) => pin.x !== null && pin.y !== null);
});

const pinsWithoutCoords = computed(() => {
  return workingPins.value.filter((pin) => pin.x === null || pin.y === null);
});

//pin dragging methods

//dragging a pin from the palette or the canvas
const onDragStart = (event, pin) => {
  pin.beingDragged = true;
  draggedPin.value = pin;
  event.dataTransfer.setData("application/json", JSON.stringify(pin));
  //create an element to use as the drag image
  const dragImage = document.createElement("div");
  dragImage.style.width = "20px";
  dragImage.style.height = "20px";
  dragImage.style.backgroundColor = "#000";
  dragImage.style.border = "3px solid white";
  dragImage.style.borderRadius = "50%";
  dragImage.style.position = "absolute";
  dragImage.style.top = "-100000px";
  dragImage.classList.add("dragImage");
  document.body.appendChild(dragImage);
  event.dataTransfer.setDragImage(dragImage, 10, 10);
};



const onragOverThrottleLastFired = ref(0);

//prevent default dragover behaviour
const onDragOver = (event) => {
  event.preventDefault();

  const now = Date.now();
  if (now - onragOverThrottleLastFired.value < 100) {
    return;
  }
  onragOverThrottleLastFired.value = now;


  const canvasRect = canvas.value.getBoundingClientRect();
  if(!pinToolTip.value || !canvasRect || draggedPin.value === null) {
    return;
  }

  mouseX.value = event.clientX - canvasRect.left;
  mouseY.value = event.clientY - canvasRect.top;

  pinToolTip.value.style.top = `${event.clientY - canvasRect.top - 20}px`;
  pinToolTip.value.style.left = `${event.clientX - canvasRect.left + 10}px`;
  pinToolTip.value.style.opacity = 1;
};

const onDrop = (event, target) => {
  event.preventDefault();
  const pin = JSON.parse(event.dataTransfer.getData("application/json"));

  const x = mouseX.value
  const y = mouseY.value

  console.log("event", event.target)
  console.log("dropped", pin.id, "on", target, "at", x, y);

  pin.x = target === "canvas" ? x : null;
  pin.y = target === "canvas" ? y : null;

  //conver the x and y to percentages of the canvas size
  if (target === "canvas") {
    pin.x = (x / canvasImage.value.width) * 100;
    pin.y = (y / canvasImage.value.height) * 100;
  }

  workingPins.value = workingPins.value.map((p) => (p.id === pin.id ? pin : p));

  const dragImage = document.querySelector(".dragImage");
  if (dragImage) {
    dragImage.remove();
  }

  workingPins.value = workingPins.value.map((p) => {
    if (p.id === pin.id) {
      p.beingDragged = false;
    }
    return p;
  });

  saveData.value = workingPins.value;

  draggedPin.value = null;
  pinToolTip.value.style.opacity = 0;
};

const truncate = (str, n = 20) => {
  return str.length > n ? str.substr(0, n - 1) + "..." : str;
};

</script>
<template>
  <div>
    <input type="hidden" :value="pinData" :name="`fields[${name}][vuedata]`" />

    <div class="pinPallete" @dragover="onDragOver" @drop="(e) => onDrop(e, 'palette')">
      <div class="pin" :class="{ hide: pin.x || pin.y || pin.beingDragged, noDrag: (pin.x || pin.y) && !pin.beingDragged  }" :key="pin.id" v-for="pin in workingPins" draggable="true" @dragstart="(e) => onDragStart(e, pin)">
        <div class="label">{{ truncate( pin.label) }}</div>
      </div>
    </div>

    <div class="canvas" ref="canvas" @dragover="onDragOver" @drop="(e) => onDrop(e, 'canvas')">
      <div class="backdrop">
        <img :src="canvasURL" alt="" ref="canvasImage" />
      </div>
      <div class="pins">
        <div v-for="pin in pinsWithCoords" :key="pin.id" class="pin" :class="{ hide: pin.beingDragged }" :style="{ top: `${pin.y}%`, left: `${pin.x}%` }" draggable="true" @dragover.prevent @drop.prevent @dragstart="(e) => onDragStart(e, pin)">
          <div class="label">{{ pin.label.slice(0, labelLengthLimit) }}{{ pin.label.length > labelLengthLimit ? "..." : "" }}</div>
        </div>
      </div>
      <div class="pin-tool-tip" ref="pinToolTip">
        {{ draggedPin ? draggedPin.label : "" }}
      </div>
    </div>
  </div>
</template>
