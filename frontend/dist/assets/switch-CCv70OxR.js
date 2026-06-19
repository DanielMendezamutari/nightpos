import{r as m,o as h,c as v,a as t,d as o,x as p,N as r,g as f,w as d,e as i,C as O,b as n,t as y,F as b,y as L,j as $,h as S}from"./index-BWGl88CJ.js";import{_ as D}from"./AppCardCode-P4aUWHBJ.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-eWUp0p6U.js";import"./VCardText-Q9Lg-4An.js";const C={class:"demo-space-x"},U={__name:"DemoSwitchStates",setup(w){const e=m("on"),a=m("on"),c=m(!0);return(l,s)=>(h(),v("div",C,[t(r,{modelValue:o(e),"onUpdate:modelValue":s[0]||(s[0]=u=>p(e)?e.value=u:null),value:"on",label:"On"},null,8,["modelValue"]),t(r,{label:"Off"}),t(r,{modelValue:o(a),"onUpdate:modelValue":s[1]||(s[1]=u=>p(a)?a.value=u:null),value:"on",disabled:"",label:"On disabled"},null,8,["modelValue"]),t(r,{disabled:"",label:"Off disabled"}),t(r,{modelValue:o(c),"onUpdate:modelValue":s[2]||(s[2]=u=>p(c)?c.value=u:null),loading:"warning",label:`${o(c)?"On":"Off"} loading`},null,8,["modelValue","label"])]))}},T={class:"demo-space-x"},J={__name:"DemoSwitchTrueAndFalseValue",setup(w){const e=m(1),a=m("Show");return(c,l)=>(h(),v("div",T,[t(r,{modelValue:o(e),"onUpdate:modelValue":l[0]||(l[0]=s=>p(e)?e.value=s:null),label:o(e).toString(),"true-value":1,"false-value":0},null,8,["modelValue","label"]),t(r,{modelValue:o(a),"onUpdate:modelValue":l[1]||(l[1]=s=>p(a)?a.value=s:null),label:o(a).toString(),"true-value":"Show","false-value":"Hide"},null,8,["modelValue","label"])]))}},A={__name:"DemoSwitchLabelSlot",setup(w){const e=m(!1);return(a,c)=>(h(),f(r,{modelValue:o(e),"onUpdate:modelValue":c[0]||(c[0]=l=>p(e)?e.value=l:null)},{label:d(()=>[i(" Turn on the progress: "),t(O,{indeterminate:o(e),size:"24",class:"ms-2"},null,8,["indeterminate"])]),_:1},8,["modelValue"]))}},F={class:"demo-space-x"},M={class:"mt-2 mb-0"},z={__name:"DemoSwitchModelAsArray",setup(w){const e=m(["John"]);return(a,c)=>(h(),v(b,null,[n("div",F,[t(r,{modelValue:o(e),"onUpdate:modelValue":c[0]||(c[0]=l=>p(e)?e.value=l:null),label:"John",value:"John"},null,8,["modelValue"]),t(r,{modelValue:o(e),"onUpdate:modelValue":c[1]||(c[1]=l=>p(e)?e.value=l:null),label:"Jacob",value:"Jacob"},null,8,["modelValue"])]),n("p",M,y(o(e)),1)],64))}},I={class:"demo-space-x"},P={__name:"DemoSwitchColors",setup(w){const e=m(["Primary","Secondary","Success","Info","Warning","Error"]),a=m(["Primary","Secondary","Success","Info","Warning","Error"]);return(c,l)=>(h(),v("div",I,[(h(!0),v(b,null,L(o(a),s=>(h(),f(r,{key:s,modelValue:o(e),"onUpdate:modelValue":l[0]||(l[0]=u=>p(e)?e.value=u:null),label:s,value:s,color:s.toLowerCase()},null,8,["modelValue","label","value","color"]))),128))]))}},j={class:"demo-space-x"},B={__name:"DemoSwitchInset",setup(w){const e=m(!0),a=m(!1);return(c,l)=>(h(),v("div",j,[t(r,{modelValue:o(e),"onUpdate:modelValue":l[0]||(l[0]=s=>p(e)?e.value=s:null),inset:!1,label:`Switch 1: ${o(e).toString()}`},null,8,["modelValue","label"]),t(r,{modelValue:o(a),"onUpdate:modelValue":l[1]||(l[1]=s=>p(a)?a.value=s:null),inset:!1,label:`Switch 2: ${o(a).toString()}`},null,8,["modelValue","label"])]))}},E={class:"demo-space-x"},k={__name:"DemoSwitchBasic",setup(w){const e=m(!0),a=m(!1),c=l=>{const s=l.toString();return s.charAt(0).toUpperCase()+s.slice(1)};return(l,s)=>(h(),v("div",E,[t(r,{modelValue:o(e),"onUpdate:modelValue":s[0]||(s[0]=u=>p(e)?e.value=u:null),label:c(o(e))},null,8,["modelValue","label"]),t(r,{modelValue:o(a),"onUpdate:modelValue":s[1]||(s[1]=u=>p(a)?a.value=u:null),label:c(o(a))},null,8,["modelValue","label"])]))}},W={ts:`<script lang="ts" setup>
const toggleSwitch = ref(true)
const toggleFalseSwitch = ref(false)

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="toggleSwitch"
      :label="capitalizedLabel(toggleSwitch)"
    />

    <VSwitch
      v-model="toggleFalseSwitch"
      :label="capitalizedLabel(toggleFalseSwitch)"
    />
  </div>
</template>
`,js:`<script setup>
const toggleSwitch = ref(true)
const toggleFalseSwitch = ref(false)

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="toggleSwitch"
      :label="capitalizedLabel(toggleSwitch)"
    />

    <VSwitch
      v-model="toggleFalseSwitch"
      :label="capitalizedLabel(toggleFalseSwitch)"
    />
  </div>
</template>
`},H={ts:`<script lang="ts" setup>
const selectedSwitch = ref(['Primary', 'Secondary', 'Success', 'Info', 'Warning', 'Error'])
const switches = ref(['Primary', 'Secondary', 'Success', 'Info', 'Warning', 'Error'])
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-for="item in switches"
      :key="item"
      v-model="selectedSwitch"
      :label="item"
      :value="item"
      :color="item.toLowerCase()"
    />
  </div>
</template>
`,js:`<script setup>
const selectedSwitch = ref([
  'Primary',
  'Secondary',
  'Success',
  'Info',
  'Warning',
  'Error',
])

const switches = ref([
  'Primary',
  'Secondary',
  'Success',
  'Info',
  'Warning',
  'Error',
])
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-for="item in switches"
      :key="item"
      v-model="selectedSwitch"
      :label="item"
      :value="item"
      :color="item.toLowerCase()"
    />
  </div>
</template>
`},N={ts:`<script lang="ts" setup>
const insetSwitch1 = ref(true)
const insetSwitch2 = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="insetSwitch1"
      :inset="false"
      :label="\`Switch 1: \${insetSwitch1.toString()}\`"
    />
    <VSwitch
      v-model="insetSwitch2"
      :inset="false"
      :label="\`Switch 2: \${insetSwitch2.toString()}\`"
    />
  </div>
</template>
`,js:`<script setup>
const insetSwitch1 = ref(true)
const insetSwitch2 = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="insetSwitch1"
      :inset="false"
      :label="\`Switch 1: \${insetSwitch1.toString()}\`"
    />
    <VSwitch
      v-model="insetSwitch2"
      :inset="false"
      :label="\`Switch 2: \${insetSwitch2.toString()}\`"
    />
  </div>
</template>
`},R={ts:`<script lang="ts" setup>
const switchMe = ref(false)
<\/script>

<template>
  <VSwitch v-model="switchMe">
    <template #label>
      Turn on the progress: <VProgressCircular
        :indeterminate="switchMe"
        size="24"
        class="ms-2"
      />
    </template>
  </VSwitch>
</template>
`,js:`<script setup>
const switchMe = ref(false)
<\/script>

<template>
  <VSwitch v-model="switchMe">
    <template #label>
      Turn on the progress: <VProgressCircular
        :indeterminate="switchMe"
        size="24"
        class="ms-2"
      />
    </template>
  </VSwitch>
</template>
`},q={ts:`<script lang="ts" setup>
const people = ref(['John'])
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="people"
      label="John"
      value="John"
    />

    <VSwitch
      v-model="people"
      label="Jacob"
      value="Jacob"
    />
  </div>

  <p class="mt-2 mb-0">
    {{ people }}
  </p>
</template>
`,js:`<script setup>
const people = ref(['John'])
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="people"
      label="John"
      value="John"
    />

    <VSwitch
      v-model="people"
      label="Jacob"
      value="Jacob"
    />
  </div>

  <p class="mt-2 mb-0">
    {{ people }}
  </p>
</template>
`},G={ts:`<script setup lang="ts">
const switchOn = ref('on')
const switchOnDisabled = ref('on')
const switchOnLoading = ref(true)
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="switchOn"
      value="on"
      label="On"
    />

    <VSwitch label="Off" />

    <VSwitch
      v-model="switchOnDisabled"
      value="on"
      disabled
      label="On disabled"
    />

    <VSwitch
      disabled
      label="Off disabled"
    />

    <VSwitch
      v-model="switchOnLoading"
      loading="warning"
      :label="\`\${switchOnLoading ? 'On' : 'Off'} loading\`"
    />
  </div>
</template>
`,js:`<script setup>
const switchOn = ref('on')
const switchOnDisabled = ref('on')
const switchOnLoading = ref(true)
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="switchOn"
      value="on"
      label="On"
    />

    <VSwitch label="Off" />

    <VSwitch
      v-model="switchOnDisabled"
      value="on"
      disabled
      label="On disabled"
    />

    <VSwitch
      disabled
      label="Off disabled"
    />

    <VSwitch
      v-model="switchOnLoading"
      loading="warning"
      :label="\`\${switchOnLoading ? 'On' : 'Off'} loading\`"
    />
  </div>
</template>
`},K={ts:`<script lang="ts" setup>
const switch1 = ref(1)
const switch2 = ref('Show')
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="switch1"
      :label="switch1.toString()"
      :true-value="1"
      :false-value="0"
    />

    <VSwitch
      v-model="switch2"
      :label="switch2.toString()"
      true-value="Show"
      false-value="Hide"
    />
  </div>
</template>
`,js:`<script setup>
const switch1 = ref(1)
const switch2 = ref('Show')
<\/script>

<template>
  <div class="demo-space-x">
    <VSwitch
      v-model="switch1"
      :label="switch1.toString()"
      :true-value="1"
      :false-value="0"
    />

    <VSwitch
      v-model="switch2"
      :label="switch2.toString()"
      true-value="Show"
      false-value="Hide"
    />
  </div>
</template>
`},Q=n("p",null,[i("A "),n("code",null,"v-switch"),i(" in its simplest form provides a toggle between 2 values.")],-1),X=n("p",null,[i("To change the default "),n("code",null,"inset"),i(" switch, simply modify the inset prop to a "),n("code",null,"false"),i(" value.")],-1),Y=n("p",null,[i("Switches can be colored by using any of the builtin colors and contextual names using the "),n("code",null,"color"),i(" prop.")],-1),Z=n("p",null,[i("Multiple "),n("code",null,"v-switch"),i("'s can share the same "),n("code",null,"v-model"),i(" by using an array.")],-1),ee=n("p",null,[i("Switch labels can be defined in "),n("code",null,"label"),i(" slot - that will allow to use HTML content.")],-1),te=n("p",null,[i(" Use "),n("code",null,"false-value"),i(" and "),n("code",null,"true-value"),i(" prop to sets value for truthy and falsy state ")],-1),le=n("p",null,[n("code",null,"v-switch"),i(" can have different states such as "),n("code",null,"default"),i(", "),n("code",null,"disabled"),i(", and "),n("code",null,"loading"),i(".")],-1),ie={__name:"switch",setup(w){return(e,a)=>{const c=k,l=D,s=B,u=P,_=z,g=A,V=J,x=U;return h(),f($,null,{default:d(()=>[t(S,{cols:"12",md:"6"},{default:d(()=>[t(l,{title:"Basic",code:W},{default:d(()=>[Q,t(c)]),_:1},8,["code"])]),_:1}),t(S,{cols:"12",md:"6"},{default:d(()=>[t(l,{title:"Inset",code:N},{default:d(()=>[X,t(s)]),_:1},8,["code"])]),_:1}),t(S,{cols:"12",md:"6"},{default:d(()=>[t(l,{title:"Colors",code:H},{default:d(()=>[Y,t(u)]),_:1},8,["code"])]),_:1}),t(S,{cols:"12",md:"6"},{default:d(()=>[t(l,{title:"Model as array",code:q},{default:d(()=>[Z,t(_)]),_:1},8,["code"])]),_:1}),t(S,{cols:"12",md:"6"},{default:d(()=>[t(l,{title:"Label slot",code:R},{default:d(()=>[ee,t(g)]),_:1},8,["code"])]),_:1}),t(S,{cols:"12",md:"6"},{default:d(()=>[t(l,{title:"True and False Value",code:K},{default:d(()=>[te,t(V)]),_:1},8,["code"])]),_:1}),t(S,{cols:"12",md:"6"},{default:d(()=>[t(l,{title:"States",code:G},{default:d(()=>[le,t(x)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{ie as default};
