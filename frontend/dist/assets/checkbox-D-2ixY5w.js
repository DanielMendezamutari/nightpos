import{r as p,o as h,c as f,a as e,w as i,h as x,d as a,x as u,j as v,F as C,g as V,b as s,e as d,am as S,a0 as O,t as I,y as U}from"./index-HMn0UlBQ.js";import{V as b}from"./VCheckbox-B6R1gPFe.js";import{V as g}from"./VTextField-hJzug-iJ.js";import{V as $}from"./VTooltip-B-JM6zF4.js";import{_ as z}from"./AppCardCode-B6BaSGcr.js";import"./VCheckboxBtn-D4HgEi4c.js";/* empty css                   */import"./VCounter-DQjbVkg-.js";import"./VField-DyB4TAK6.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-kWWeRP1d.js";import"./VCardText-DUfc7MtM.js";const D={__name:"DemoCheckboxInlineTextField",setup(k){const o=p(!0),t=p(!1);return(r,l)=>(h(),f(C,null,[e(v,null,{default:i(()=>[e(x,{sm:"1",cols:"2"},{default:i(()=>[e(b,{modelValue:a(o),"onUpdate:modelValue":l[0]||(l[0]=c=>u(o)?o.value=c:null)},null,8,["modelValue"])]),_:1}),e(x,{sm:"11",cols:"10"},{default:i(()=>[e(g,{label:"Include files",placeholder:"Placeholder Text"})]),_:1})]),_:1}),e(v,null,{default:i(()=>[e(x,{cols:"2",sm:"1"},{default:i(()=>[e(b,{modelValue:a(t),"onUpdate:modelValue":l[1]||(l[1]=c=>u(t)?t.value=c:null)},null,8,["modelValue"])]),_:1}),e(x,{cols:"10",sm:"11"},{default:i(()=>[e(g,{disabled:!a(t),label:"I only work if you check the box",placeholder:"Placeholder Text"},null,8,["disabled"])]),_:1})]),_:1})],64))}},J={__name:"DemoCheckboxLabelSlot",setup(k){const o=p(!1);return(t,r)=>(h(),V(b,{modelValue:a(o),"onUpdate:modelValue":r[1]||(r[1]=l=>u(o)?o.value=l:null)},{label:i(()=>[s("div",null,[d(" I agree that "),e($,{location:"bottom"},{activator:i(({props:l})=>[s("a",S({href:"https://vuetifyjs.com/",target:"_blank",rel:"noopener noreferrer"},l,{onClick:r[0]||(r[0]=O(()=>{},["stop"]))})," Vuetify ",16)]),default:i(()=>[d(" Opens in new window ")]),_:1}),d(" is awesome ")])]),_:1},8,["modelValue"]))}},A={class:"demo-space-x"},F={__name:"DemoCheckboxStates",setup(k){const o=p(!0),t=p(!0),r=p(!0),l=p(!1);return(c,n)=>(h(),f("div",A,[e(b,{modelValue:a(o),"onUpdate:modelValue":n[0]||(n[0]=m=>u(o)?o.value=m:null),label:"On"},null,8,["modelValue"]),e(b,{modelValue:a(l),"onUpdate:modelValue":n[1]||(n[1]=m=>u(l)?l.value=m:null),label:"Off"},null,8,["modelValue"]),e(b,{indeterminate:a(t),"onUpdate:indeterminate":n[2]||(n[2]=m=>u(t)?t.value=m:null),modelValue:a(t),"onUpdate:modelValue":n[3]||(n[3]=m=>u(t)?t.value=m:null),label:"Indeterminate"},null,8,["indeterminate","modelValue"]),e(b,{"model-value":a(r),disabled:"",label:"On disabled"},null,8,["model-value"]),e(b,{disabled:"",label:"Off disabled"})]))}},j={class:"demo-space-x"},E={__name:"DemoCheckboxCheckboxValue",setup(k){const o=p(1),t=p("Show");return(r,l)=>(h(),f("div",j,[e(b,{modelValue:a(o),"onUpdate:modelValue":l[0]||(l[0]=c=>u(o)?o.value=c:null),"true-value":1,"false-value":0,label:`${a(o).toString()}`},null,8,["modelValue","label"]),e(b,{modelValue:a(t),"onUpdate:modelValue":l[1]||(l[1]=c=>u(t)?t.value=c:null),"true-value":"Show","false-value":"Hide",color:"success",label:`${a(t).toString()}`},null,8,["modelValue","label"])]))}},P={class:"demo-space-x"},R={__name:"DemoCheckboxIcon",setup(k){const o=p(!0),t=p(!0),r=p(!0),l=c=>{const n=c.toString();return n.charAt(0).toUpperCase()+n.slice(1)};return(c,n)=>(h(),f("div",P,[e(b,{modelValue:a(o),"onUpdate:modelValue":n[0]||(n[0]=m=>u(o)?o.value=m:null),label:l(a(o)),"true-icon":"ri-check-line","false-icon":"ri-close-line"},null,8,["modelValue","label"]),e(b,{modelValue:a(t),"onUpdate:modelValue":n[1]||(n[1]=m=>u(t)?t.value=m:null),label:l(a(t)),"true-icon":"ri-alarm-line","false-icon":"ri-alarm-line",color:"success"},null,8,["modelValue","label"]),e(b,{modelValue:a(r),"onUpdate:modelValue":n[2]||(n[2]=m=>u(r)?r.value=m:null),label:l(a(r)),"true-icon":"ri-checkbox-circle-fill","false-icon":"ri-close-circle-fill",color:"error"},null,8,["modelValue","label"])]))}},B={class:"demo-space-x"},M={class:"mt-1"},H={__name:"DemoCheckboxModelAsArray",setup(k){const o=p(["John"]);return(t,r)=>(h(),f(C,null,[s("div",B,[e(b,{modelValue:a(o),"onUpdate:modelValue":r[0]||(r[0]=l=>u(o)?o.value=l:null),label:"John",value:"John"},null,8,["modelValue"]),e(b,{modelValue:a(o),"onUpdate:modelValue":r[1]||(r[1]=l=>u(o)?o.value=l:null),label:"Jacob",color:"success",value:"Jacob"},null,8,["modelValue"]),e(b,{modelValue:a(o),"onUpdate:modelValue":r[2]||(r[2]=l=>u(o)?o.value=l:null),label:"Johnson",color:"info",value:"Johnson"},null,8,["modelValue"])]),s("p",M,I(a(o)),1)],64))}},N={class:"demo-space-x"},W={__name:"DemoCheckboxColors",setup(k){const o=p(["Primary","Secondary","Success","Info","Warning","Error"]),t=p([]);return(r,l)=>(h(),f("div",N,[(h(!0),f(C,null,U(a(o),c=>(h(),V(b,{key:c,modelValue:a(t),"onUpdate:modelValue":l[0]||(l[0]=n=>u(t)?t.value=n:null),label:c,color:c.toLowerCase(),value:c},null,8,["modelValue","label","color","value"]))),128))]))}},Y={class:"demo-space-x"},q={__name:"DemoCheckboxDensity",setup(k){const o=p(!0),t=p(!1),r=l=>{const c=l.toString();return c.charAt(0).toUpperCase()+c.slice(1)};return(l,c)=>(h(),f("div",Y,[e(b,{modelValue:a(o),"onUpdate:modelValue":c[0]||(c[0]=n=>u(o)?o.value=n:null),density:"compact",label:r(a(o))},null,8,["modelValue","label"]),e(b,{modelValue:a(t),"onUpdate:modelValue":c[1]||(c[1]=n=>u(t)?t.value=n:null),density:"compact",label:r(a(t))},null,8,["modelValue","label"])]))}},G={class:"demo-space-x"},K={__name:"DemoCheckboxBasic",setup(k){const o=p(!0),t=p(!1),r=l=>{const c=l.toString();return c.charAt(0).toUpperCase()+c.slice(1)};return(l,c)=>(h(),f("div",G,[e(b,{modelValue:a(o),"onUpdate:modelValue":c[0]||(c[0]=n=>u(o)?o.value=n:null),label:r(a(o))},null,8,["modelValue","label"]),e(b,{modelValue:a(t),"onUpdate:modelValue":c[1]||(c[1]=n=>u(t)?t.value=n:null),label:r(a(t))},null,8,["modelValue","label"])]))}},Q={ts:`<script lang="ts" setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`,js:`<script setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`},X={ts:`<script lang="ts" setup>
const checkbox = ref(1)
const checkboxString = ref('Show')
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkbox"
      :true-value="1"
      :false-value="0"
      :label="\`\${checkbox.toString()}\`"
    />

    <VCheckbox
      v-model="checkboxString"
      true-value="Show"
      false-value="Hide"
      color="success"
      :label="\`\${checkboxString.toString()}\`"
    />
  </div>
</template>
`,js:`<script setup>
const checkbox = ref(1)
const checkboxString = ref('Show')
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkbox"
      :true-value="1"
      :false-value="0"
      :label="\`\${checkbox.toString()}\`"
    />

    <VCheckbox
      v-model="checkboxString"
      true-value="Show"
      false-value="Hide"
      color="success"
      :label="\`\${checkboxString.toString()}\`"
    />
  </div>
</template>
`},Z={ts:`<script lang="ts" setup>
const colorCheckbox = ref(['Primary', 'Secondary', 'Success', 'Info', 'Warning', 'Error'])
const selectedCheckbox = ref([])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-for="color in colorCheckbox"
      :key="color"
      v-model="selectedCheckbox"
      :label="color"
      :color="color.toLowerCase()"
      :value="color"
    />
  </div>
</template>
`,js:`<script setup>
const colorCheckbox = ref([
  'Primary',
  'Secondary',
  'Success',
  'Info',
  'Warning',
  'Error',
])

const selectedCheckbox = ref([])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-for="color in colorCheckbox"
      :key="color"
      v-model="selectedCheckbox"
      :label="color"
      :color="color.toLowerCase()"
      :value="color"
    />
  </div>
</template>
`},ee={ts:`<script lang="ts" setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      density="compact"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      density="compact"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`,js:`<script setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      density="compact"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      density="compact"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`},le={ts:`<script lang="ts" setup>
const toggleCheckboxOne = ref(true)
const toggleCheckboxTwo = ref(true)
const toggleCheckboxThree = ref(true)

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckboxOne"
      :label="capitalizedLabel(toggleCheckboxOne)"
      true-icon="ri-check-line"
      false-icon="ri-close-line"
    />

    <VCheckbox
      v-model="toggleCheckboxTwo"
      :label="capitalizedLabel(toggleCheckboxTwo)"
      true-icon="ri-alarm-line"
      false-icon="ri-alarm-line"
      color="success"
    />

    <VCheckbox
      v-model="toggleCheckboxThree"
      :label="capitalizedLabel(toggleCheckboxThree)"
      true-icon="ri-checkbox-circle-fill"
      false-icon="ri-close-circle-fill"
      color="error"
    />
  </div>
</template>
`,js:`<script setup>
const toggleCheckboxOne = ref(true)
const toggleCheckboxTwo = ref(true)
const toggleCheckboxThree = ref(true)

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckboxOne"
      :label="capitalizedLabel(toggleCheckboxOne)"
      true-icon="ri-check-line"
      false-icon="ri-close-line"
    />

    <VCheckbox
      v-model="toggleCheckboxTwo"
      :label="capitalizedLabel(toggleCheckboxTwo)"
      true-icon="ri-alarm-line"
      false-icon="ri-alarm-line"
      color="success"
    />

    <VCheckbox
      v-model="toggleCheckboxThree"
      :label="capitalizedLabel(toggleCheckboxThree)"
      true-icon="ri-checkbox-circle-fill"
      false-icon="ri-close-circle-fill"
      color="error"
    />
  </div>
</template>
`},oe={ts:`<script lang="ts" setup>
const includeFiles = ref(true)
const isInputEnabled = ref(false)
<\/script>

<template>
  <VRow>
    <VCol
      sm="1"
      cols="2"
    >
      <VCheckbox v-model="includeFiles" />
    </VCol>

    <VCol
      sm="11"
      cols="10"
    >
      <VTextField
        label="Include files"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>

  <VRow>
    <VCol
      cols="2"
      sm="1"
    >
      <VCheckbox v-model="isInputEnabled" />
    </VCol>

    <VCol
      cols="10"
      sm="11"
    >
      <VTextField
        :disabled="!isInputEnabled"
        label="I only work if you check the box"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const includeFiles = ref(true)
const isInputEnabled = ref(false)
<\/script>

<template>
  <VRow>
    <VCol
      sm="1"
      cols="2"
    >
      <VCheckbox v-model="includeFiles" />
    </VCol>

    <VCol
      sm="11"
      cols="10"
    >
      <VTextField
        label="Include files"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>

  <VRow>
    <VCol
      cols="2"
      sm="1"
    >
      <VCheckbox v-model="isInputEnabled" />
    </VCol>

    <VCol
      cols="10"
      sm="11"
    >
      <VTextField
        :disabled="!isInputEnabled"
        label="I only work if you check the box"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`},te={ts:`<script lang="ts" setup>
const checkbox = ref(false)
<\/script>

<template>
  <VCheckbox v-model="checkbox">
    <template #label>
      <div>
        I agree that
        <VTooltip location="bottom">
          <template #activator="{ props }">
            <a
              href="https://vuetifyjs.com/"
              target="_blank"
              rel="noopener noreferrer"
              v-bind="props"
              @click.stop
            >
              Vuetify
            </a>
          </template>
          Opens in new window
        </VTooltip>
        is awesome
      </div>
    </template>
  </VCheckbox>
</template>
`,js:`<script setup>
const checkbox = ref(false)
<\/script>

<template>
  <VCheckbox v-model="checkbox">
    <template #label>
      <div>
        I agree that
        <VTooltip location="bottom">
          <template #activator="{ props }">
            <a
              href="https://vuetifyjs.com/"
              target="_blank"
              rel="noopener noreferrer"
              v-bind="props"
              @click.stop
            >
              Vuetify
            </a>
          </template>
          Opens in new window
        </VTooltip>
        is awesome
      </div>
    </template>
  </VCheckbox>
</template>
`},ce={ts:`<script lang="ts" setup>
const selected = ref(['John'])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="selected"
      label="John"
      value="John"
    />

    <VCheckbox
      v-model="selected"
      label="Jacob"
      color="success"
      value="Jacob"
    />

    <VCheckbox
      v-model="selected"
      label="Johnson"
      color="info"
      value="Johnson"
    />
  </div>

  <p class="mt-1">
    {{ selected }}
  </p>
</template>
`,js:`<script setup>
const selected = ref(['John'])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="selected"
      label="John"
      value="John"
    />

    <VCheckbox
      v-model="selected"
      label="Jacob"
      color="success"
      value="Jacob"
    />

    <VCheckbox
      v-model="selected"
      label="Johnson"
      color="info"
      value="Johnson"
    />
  </div>

  <p class="mt-1">
    {{ selected }}
  </p>
</template>
`},ae={ts:`<script setup lang="ts">
const toggleCheckbox = ref(true)
const toggleIndeterminateCheckbox = ref(true)
const disabledCheckbox = ref(true)
const toggleOffCheckbox = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckbox"
      label="On"
    />

    <VCheckbox
      v-model="toggleOffCheckbox"
      label="Off"
    />

    <VCheckbox
      v-model:indeterminate="toggleIndeterminateCheckbox"
      v-model="toggleIndeterminateCheckbox"
      label="Indeterminate"
    />

    <VCheckbox
      :model-value="disabledCheckbox"
      disabled
      label="On disabled"
    />

    <VCheckbox
      disabled
      label="Off disabled"
    />
  </div>
</template>
`,js:`<script setup>
const toggleCheckbox = ref(true)
const toggleIndeterminateCheckbox = ref(true)
const disabledCheckbox = ref(true)
const toggleOffCheckbox = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckbox"
      label="On"
    />

    <VCheckbox
      v-model="toggleOffCheckbox"
      label="Off"
    />

    <VCheckbox
      v-model:indeterminate="toggleIndeterminateCheckbox"
      v-model="toggleIndeterminateCheckbox"
      label="Indeterminate"
    />

    <VCheckbox
      :model-value="disabledCheckbox"
      disabled
      label="On disabled"
    />

    <VCheckbox
      disabled
      label="Off disabled"
    />
  </div>
</template>
`},se=s("p",null,[s("code",null,"v-checkbox"),d(" in its simplest form provides a toggle between 2 values.")],-1),ne=s("p",null,[d("Use "),s("code",null,"density"),d(" prop to reduces the input height. Available options are: "),s("code",null,"default"),d(", "),s("code",null,"comfortable"),d(", and "),s("code",null,"compact"),d(".")],-1),re=s("p",null,[d("Checkboxes can be colored by using any of the builtin colors and contextual names using the "),s("code",null,"color"),d(" prop.")],-1),de=s("p",null,[d("Multiple "),s("code",null,"v-checkbox"),d("'s can share the same "),s("code",null,"v-model"),d(" by using an array.")],-1),ie=s("p",null,[d("Use "),s("code",null,"false-icon"),d(" and "),s("code",null,"true-icon"),d(" prop to change the icon on the checkbox.")],-1),be=s("p",null,[d("Use "),s("code",null,"false-value"),d(" and "),s("code",null,"true-value"),d(" prop to sets value for truthy and falsy state")],-1),ue=s("p",null,[s("code",null,"v-checkbox"),d(" can have different states such as "),s("code",null,"default"),d(", "),s("code",null,"disabled"),d(", and "),s("code",null,"indeterminate"),d(".")],-1),pe=s("p",null,[d("Checkbox labels can be defined in "),s("code",null,"label"),d(" slot - that will allow to use HTML content.")],-1),me=s("p",null,[d("You can place "),s("code",null,"v-checkbox"),d(" in line with other components such as "),s("code",null,"v-text-field"),d(".")],-1),ye={__name:"checkbox",setup(k){return(o,t)=>{const r=K,l=z,c=q,n=W,m=H,_=R,T=E,w=F,L=J,y=D;return h(),V(v,{class:"match-height"},{default:i(()=>[e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Basic",code:Q},{default:i(()=>[se,e(r)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Density",code:ee},{default:i(()=>[ne,e(c)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Colors",code:Z},{default:i(()=>[re,e(n)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Model as array",code:ce},{default:i(()=>[de,e(m)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Icon",code:le},{default:i(()=>[ie,e(_)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Checkbox Value",code:X},{default:i(()=>[be,e(T)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"States",code:ae},{default:i(()=>[ue,e(w)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Label Slot",code:te},{default:i(()=>[pe,e(L)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12",md:"6"},{default:i(()=>[e(l,{title:"Inline text-field",code:oe},{default:i(()=>[me,e(y)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{ye as default};
