import{_ as s}from"./AppDateTimePicker-FKK5Iq1A.js";import{r as u,o as r,g as i,d as c,x as _,w as o,j as b,a as l,h as d}from"./index-HMn0UlBQ.js";import{_ as F}from"./AppCardCode-B6BaSGcr.js";import"./VField-DyB4TAK6.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-kWWeRP1d.js";import"./VCardText-DUfc7MtM.js";const h={__name:"DemoDateTimePickerInline",setup(p){const e=u("");return(m,a)=>{const t=s;return r(),i(t,{modelValue:c(e),"onUpdate:modelValue":a[0]||(a[0]=n=>_(e)?e.value=n:null),label:"Inline",placeholder:"Select Date",config:{inline:!0}},null,8,["modelValue"])}}},A={__name:"DemoDateTimePickerDisabledRange",setup(p){const e=new Date,m=e.toLocaleString("default",{month:"2-digit"}),a=e.getFullYear(),t=u("");return(n,D)=>{const f=s;return r(),i(f,{modelValue:c(t),"onUpdate:modelValue":D[0]||(D[0]=g=>_(t)?t.value=g:null),label:"Disabled Range",placeholder:"Select date",config:{dateFormat:"Y-m-d",disable:[{from:`${c(a)}-${c(m)}-20`,to:`${c(a)}-${c(m)}-25`}]}},null,8,["modelValue","config"])}}},S={__name:"DemoDateTimePickerHumanFriendly",setup(p){const e=u("");return(m,a)=>{const t=s;return r(),i(t,{modelValue:c(e),"onUpdate:modelValue":a[0]||(a[0]=n=>_(e)?e.value=n:null),label:"Human Friendly",placeholder:"Select date",config:{altInput:!0,altFormat:"F j, Y",dateFormat:"Y-m-d"}},null,8,["modelValue"])}}},V={__name:"DemoDateTimePickerRange",setup(p){const e=u("");return(m,a)=>{const t=s;return r(),i(t,{modelValue:c(e),"onUpdate:modelValue":a[0]||(a[0]=n=>_(e)?e.value=n:null),label:"Range",placeholder:"Select date",config:{mode:"range"}},null,8,["modelValue"])}}},Y={__name:"DemoDateTimePickerMultipleDates",setup(p){const e=u("");return(m,a)=>{const t=s;return r(),i(t,{modelValue:c(e),"onUpdate:modelValue":a[0]||(a[0]=n=>_(e)?e.value=n:null),label:"Multiple Dates",placeholder:"Select date",config:{mode:"multiple",dateFormat:"Y-m-d"}},null,8,["modelValue"])}}},v={__name:"DemoDateTimePickerDateAndTime",setup(p){const e=u("");return(m,a)=>{const t=s;return r(),i(t,{modelValue:c(e),"onUpdate:modelValue":a[0]||(a[0]=n=>_(e)?e.value=n:null),label:"Date & TIme",placeholder:"Select date and time",config:{enableTime:!0,dateFormat:"Y-m-d H:i"}},null,8,["modelValue"])}}},$={__name:"DemoDateTimePickerTimePicker",setup(p){const e=u("");return(m,a)=>{const t=s;return r(),i(t,{modelValue:c(e),"onUpdate:modelValue":a[0]||(a[0]=n=>_(e)?e.value=n:null),label:"Time picker",placeholder:"Select time",config:{enableTime:!0,noCalendar:!0,dateFormat:"H:i"}},null,8,["modelValue"])}}},R={__name:"DemoDateTimePickerBasic",setup(p){const e=u("");return(m,a)=>{const t=s;return r(),i(t,{modelValue:c(e),"onUpdate:modelValue":a[0]||(a[0]=n=>_(e)?e.value=n:null),label:"Default",placeholder:"Select date"},null,8,["modelValue"])}}},w={ts:`<script setup lang="ts">
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Default"
    placeholder="Select date"
  />
</template>
`,js:`<script setup>
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Default"
    placeholder="Select date"
  />
</template>
`},M={ts:`<script setup lang="ts">
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Date & TIme"
    placeholder="Select date and time"
    :config="{ enableTime: true, dateFormat: 'Y-m-d H:i' }"
  />
</template>
`,js:`<script setup>
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Date & TIme"
    placeholder="Select date and time"
    :config="{ enableTime: true, dateFormat: 'Y-m-d H:i' }"
  />
</template>
`},j={ts:`<script setup lang="ts">
const now = new Date()
const currentMonth = now.toLocaleString('default', { month: '2-digit' })
const currentYear = now.getFullYear()
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Disabled Range"
    placeholder="Select date"
    :config="{ dateFormat: 'Y-m-d', disable: [{ from: \`\${currentYear}-\${currentMonth}-20\`, to: \`\${currentYear}-\${currentMonth}-25\` }] }"
  />
</template>
`,js:`<script setup>
const now = new Date()
const currentMonth = now.toLocaleString('default', { month: '2-digit' })
const currentYear = now.getFullYear()
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Disabled Range"
    placeholder="Select date"
    :config="{ dateFormat: 'Y-m-d', disable: [{ from: \`\${currentYear}-\${currentMonth}-20\`, to: \`\${currentYear}-\${currentMonth}-25\` }] }"
  />
</template>
`},x={ts:`<script setup lang="ts">
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Human Friendly"
    placeholder="Select date"
    :config="{ altInput: true, altFormat: 'F j, Y', dateFormat: 'Y-m-d' }"
  />
</template>
`,js:`<script setup>
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Human Friendly"
    placeholder="Select date"
    :config="{ altInput: true, altFormat: 'F j, Y', dateFormat: 'Y-m-d' }"
  />
</template>
`},H={ts:`<script setup lang="ts">
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Inline"
    placeholder="Select Date"
    :config="{ inline: true }"
  />
</template>
`,js:`<script setup>
const date = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="date"
    label="Inline"
    placeholder="Select Date"
    :config="{ inline: true }"
  />
</template>
`},I={ts:`<script setup lang="ts">
const multipleDate = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="multipleDate"
    label="Multiple Dates"
    placeholder="Select date"
    :config="{ mode: 'multiple', dateFormat: 'Y-m-d' }"
  />
</template>
`,js:`<script setup>
const multipleDate = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="multipleDate"
    label="Multiple Dates"
    placeholder="Select date"
    :config="{ mode: 'multiple', dateFormat: 'Y-m-d' }"
  />
</template>
`},U={ts:`<script setup lang="ts">
const dateRange = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="dateRange"
    label="Range"
    placeholder="Select date"
    :config="{ mode: 'range' }"
  />
</template>
`,js:`<script setup>
const dateRange = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="dateRange"
    label="Range"
    placeholder="Select date"
    :config="{ mode: 'range' }"
  />
</template>
`},y={ts:`<script setup lang="ts">
const time = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="time"
    label="Time picker"
    placeholder="Select time"
    :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }"
  />
</template>
`,js:`<script setup>
const time = ref('')
<\/script>

<template>
  <AppDateTimePicker
    v-model="time"
    label="Time picker"
    placeholder="Select time"
    :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }"
  />
</template>
`},G={__name:"date-time-picker",setup(p){return(e,m)=>{const a=R,t=F,n=$,D=v,f=Y,g=V,T=S,k=A,P=h;return r(),i(b,null,{default:o(()=>[l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Basic",code:w},{default:o(()=>[l(a)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Time Picker",code:y},{default:o(()=>[l(n)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Date and Time",code:M},{default:o(()=>[l(D)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Multiple Dates",code:I},{default:o(()=>[l(f)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Range",code:U},{default:o(()=>[l(g)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Human Friendly",code:x},{default:o(()=>[l(T)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Disabled Range",code:j},{default:o(()=>[l(k)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(t,{title:"Inline",code:H},{default:o(()=>[l(P)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{G as default};
