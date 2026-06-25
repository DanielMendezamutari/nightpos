import{V as c}from"./VTextarea-BjgtpQh9.js";import{r as m,o as d,g as i,d as x,x as _,_ as h,w as l,a as e,h as t,j as V,b as o,e as a}from"./index-Bfu-2vV7.js";import{_ as y}from"./AppCardCode-6WNzL3-Z.js";/* empty css                   */import"./VCounter-auAsb-33.js";import"./VField-DRZOEECb.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-BN2R1v4q.js";import"./VCardText-DBf_M7Ny.js";const j={__name:"DemoTextareaValidation",setup(p){const r=m("Hello!"),u=[s=>s.length<=25||"Max 25 characters"];return(s,n)=>(d(),i(c,{modelValue:x(r),"onUpdate:modelValue":n[0]||(n[0]=T=>_(r)?r.value=T:null),label:"Validation",rules:u,rows:"2",placeholder:"Placeholder Text"},null,8,["modelValue"]))}},k={__name:"DemoTextareaNoResize",setup(p){const r=m("Marshmallow tiramisu pie dessert gingerbread tart caramels marzipan oat cake. Muffin sesame snaps cupcake bonbon cookie tiramisu. Pudding biscuit gingerbread halvah lollipop jelly-o cookie.");return(u,s)=>(d(),i(c,{modelValue:x(r),"onUpdate:modelValue":s[0]||(s[0]=n=>_(r)?r.value=n:null),label:"Text","no-resize":"",rows:"2",placeholder:"Placeholder Text"},null,8,["modelValue"]))}},z={};function $(p,r){return d(),i(V,null,{default:l(()=>[e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"One row","auto-grow":"",rows:"1","row-height":"15",placeholder:"Placeholder Text"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{"auto-grow":"",label:"Two rows",rows:"2",placeholder:"Placeholder Text","row-height":"20"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"Three rows","auto-grow":"",rows:"3",placeholder:"Placeholder Text","row-height":"25"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{"auto-grow":"",label:"Four rows",placeholder:"Placeholder Text",rows:"4","row-height":"30"})]),_:1})]),_:1})}const A=h(z,[["render",$]]),H={};function U(p,r){return d(),i(V,null,{default:l(()=>[e(t,{cols:"12"},{default:l(()=>[e(c,{label:"Prepend-icon",rows:"1",placeholder:"Placeholder Text","prepend-icon":"ri-chat-2-line"})]),_:1}),e(t,{cols:"12"},{default:l(()=>[e(c,{"append-icon":"ri-chat-2-line",placeholder:"Placeholder Text",label:"Append-icon",rows:"1"})]),_:1}),e(t,{cols:"12"},{default:l(()=>[e(c,{"prepend-inner-icon":"ri-chat-2-line",label:"Prepend-inner-icon",placeholder:"Placeholder Text",rows:"1"})]),_:1}),e(t,{cols:"12"},{default:l(()=>[e(c,{"append-inner-icon":"ri-chat-2-line",label:"Append-inner-icon",placeholder:"Placeholder Text",rows:"1"})]),_:1})]),_:1})}const M=h(H,[["render",U]]),B={__name:"DemoTextareaCounter",setup(p){const r=m("Hello!");return(u,s)=>(d(),i(c,{modelValue:x(r),"onUpdate:modelValue":s[0]||(s[0]=n=>_(r)?r.value=n:null),counter:"",label:"Text",placeholder:"Placeholder Text"},null,8,["modelValue"]))}},G={__name:"DemoTextareaClearable",setup(p){const r=m("This is clearable text.");return(u,s)=>(d(),i(c,{modelValue:x(r),"onUpdate:modelValue":s[0]||(s[0]=n=>_(r)?r.value=n:null),clearable:"","clear-icon":"ri-close-line",label:"Text",placeholder:"Placeholder Text"},null,8,["modelValue"]))}},F={};function N(p,r){return d(),i(c,{autocomplete:"email",label:"Email",placeholder:"johndoe@email.com"})}const O=h(F,[["render",N]]),S={};function W(p,r){return d(),i(V,null,{default:l(()=>[e(t,{cols:"12"},{default:l(()=>[e(c,{disabled:"",label:"Disabled",hint:"Hint text",placeholder:"Placeholder Text",rows:"2"})]),_:1}),e(t,{cols:"12"},{default:l(()=>[e(c,{readonly:"",rows:"2",label:"Readonly",placeholder:"Placeholder Text",hint:"Hint text"})]),_:1})]),_:1})}const E=h(S,[["render",W]]),I={};function Y(p,r){return d(),i(V,null,{default:l(()=>[e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"Default",rows:"2",placeholder:"Placeholder Text"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"Solo",placeholder:"Placeholder Text",rows:"2",variant:"solo"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"Filled",rows:"2",placeholder:"Placeholder Text",variant:"filled"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"Outlined",rows:"2",placeholder:"Placeholder Text",variant:"outlined"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"Underlined",rows:"2",placeholder:"Placeholder Text",variant:"underlined"})]),_:1}),e(t,{cols:"12",sm:"6"},{default:l(()=>[e(c,{label:"Plain",rows:"2",placeholder:"Placeholder Text",variant:"plain"})]),_:1})]),_:1})}const q=h(I,[["render",Y]]),J={__name:"DemoTextareaAutoGrow",setup(p){const r=m("The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through.");return(u,s)=>(d(),i(c,{modelValue:x(r),"onUpdate:modelValue":s[0]||(s[0]=n=>_(r)?r.value=n:null),label:"Auto Grow",placeholder:"Placeholder Text","auto-grow":""},null,8,["modelValue"]))}},K={};function L(p,r){return d(),i(c,{label:"Default",placeholder:"Placeholder Text"})}const Q=h(K,[["render",L]]),X={ts:`<script setup lang="ts">
const textareaValue = ref('The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through.')
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    label="Auto Grow"
    placeholder="Placeholder Text"
    auto-grow
  />
</template>
`,js:`<script setup>
const textareaValue = ref('The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through.')
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    label="Auto Grow"
    placeholder="Placeholder Text"
    auto-grow
  />
</template>
`},Z={ts:`<template>
  <VTextarea
    label="Default"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <VTextarea
    label="Default"
    placeholder="Placeholder Text"
  />
</template>
`},ee={ts:`<template>
  <VTextarea
    autocomplete="email"
    label="Email"
    placeholder="johndoe@email.com"
  />
</template>
`,js:`<template>
  <VTextarea
    autocomplete="email"
    label="Email"
    placeholder="johndoe@email.com"
  />
</template>
`},le={ts:`<script setup lang="ts">
const textareaValue = ref('This is clearable text.')
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    clearable
    clear-icon="ri-close-line"
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const textareaValue = ref('This is clearable text.')
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    clearable
    clear-icon="ri-close-line"
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`},oe={ts:`<script lang="ts" setup>
const textareaValue = ref('Hello!')
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    counter
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const textareaValue = ref('Hello!')
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    counter
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`},ae={ts:`<template>
  <VRow>
    <VCol cols="12">
      <VTextarea
        label="Prepend-icon"
        rows="1"
        placeholder="Placeholder Text"
        prepend-icon="ri-chat-2-line"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        append-icon="ri-chat-2-line"
        placeholder="Placeholder Text"
        label="Append-icon"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        prepend-inner-icon="ri-chat-2-line"
        label="Prepend-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        append-inner-icon="ri-chat-2-line"
        label="Append-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol cols="12">
      <VTextarea
        label="Prepend-icon"
        rows="1"
        placeholder="Placeholder Text"
        prepend-icon="ri-chat-2-line"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        append-icon="ri-chat-2-line"
        placeholder="Placeholder Text"
        label="Append-icon"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        prepend-inner-icon="ri-chat-2-line"
        label="Prepend-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        append-inner-icon="ri-chat-2-line"
        label="Append-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>
  </VRow>
</template>
`},te={ts:`<script lang="ts" setup>
const value = ref('Marshmallow tiramisu pie dessert gingerbread tart caramels marzipan oat cake. Muffin sesame snaps cupcake bonbon cookie tiramisu. Pudding biscuit gingerbread halvah lollipop jelly-o cookie.')
<\/script>

<template>
  <VTextarea
    v-model="value"
    label="Text"
    no-resize
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const value = ref('Marshmallow tiramisu pie dessert gingerbread tart caramels marzipan oat cake. Muffin sesame snaps cupcake bonbon cookie tiramisu. Pudding biscuit gingerbread halvah lollipop jelly-o cookie.')
<\/script>

<template>
  <VTextarea
    v-model="value"
    label="Text"
    no-resize
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`},re={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="One row"
        auto-grow
        rows="1"
        row-height="15"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        auto-grow
        label="Two rows"
        rows="2"
        placeholder="Placeholder Text"
        row-height="20"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Three rows"
        auto-grow
        rows="3"
        placeholder="Placeholder Text"
        row-height="25"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        auto-grow
        label="Four rows"
        placeholder="Placeholder Text"
        rows="4"
        row-height="30"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="One row"
        auto-grow
        rows="1"
        row-height="15"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        auto-grow
        label="Two rows"
        rows="2"
        placeholder="Placeholder Text"
        row-height="20"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Three rows"
        auto-grow
        rows="3"
        placeholder="Placeholder Text"
        row-height="25"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        auto-grow
        label="Four rows"
        placeholder="Placeholder Text"
        rows="4"
        row-height="30"
      />
    </VCol>
  </VRow>
</template>
`},ce={ts:`<template>
  <VRow>
    <VCol cols="12">
      <VTextarea
        disabled
        label="Disabled"
        hint="Hint text"
        placeholder="Placeholder Text"
        rows="2"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        readonly
        rows="2"
        label="Readonly"
        placeholder="Placeholder Text"
        hint="Hint text"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol cols="12">
      <VTextarea
        disabled
        label="Disabled"
        hint="Hint text"
        placeholder="Placeholder Text"
        rows="2"
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        readonly
        rows="2"
        label="Readonly"
        placeholder="Placeholder Text"
        hint="Hint text"
      />
    </VCol>
  </VRow>
</template>
`},ne={ts:`<script lang="ts" setup>
const textareaValue = ref('Hello!')
const rules = [(v: string) => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    label="Validation"
    :rules="rules"
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const textareaValue = ref('Hello!')
const rules = [v => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <VTextarea
    v-model="textareaValue"
    label="Validation"
    :rules="rules"
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`},se={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Default"
        rows="2"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Solo"
        placeholder="Placeholder Text"
        rows="2"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Filled"
        rows="2"
        placeholder="Placeholder Text"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Outlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="outlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Underlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="underlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Plain"
        rows="2"
        placeholder="Placeholder Text"
        variant="plain"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Default"
        rows="2"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Solo"
        placeholder="Placeholder Text"
        rows="2"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Filled"
        rows="2"
        placeholder="Placeholder Text"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Outlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="outlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Underlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="underlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Plain"
        rows="2"
        placeholder="Placeholder Text"
        variant="plain"
      />
    </VCol>
  </VRow>
</template>
`},de=o("p",null," v-textarea in its simplest form is a multi-line text-field, useful for larger amounts of text. ",-1),ie=o("p",null,[a("When using the "),o("code",null,"auto-grow"),a(" prop, textarea's will automatically increase in size when the contained text exceeds its size.")],-1),pe=o("p",null,[a("Use "),o("code",null,"filled"),a(", "),o("code",null,"plain"),a(", "),o("code",null,"outlined"),a(", "),o("code",null,"solo"),a(" and "),o("code",null,"underlined"),a(" option of "),o("code",null,"variant"),a(" prop to change the look of file input.")],-1),ue=o("p",null,[a("Use "),o("code",null,"disabled"),a(" and "),o("code",null,"readonly"),a(" prop to change the state of textarea.")],-1),he=o("p",null,[a(" The "),o("code",null,"autocomplete"),a(" prop gives you the option to enable the browser to predict user input. ")],-1),me=o("p",null,[a("You can clear the text from a "),o("code",null,"v-textarea"),a(" by using the "),o("code",null,"clearable"),a(" prop, and customize the icon used with the "),o("code",null,"clearable-icon"),a(" prop.")],-1),xe=o("p",null,[a(" The "),o("code",null,"counter"),a(" prop informs the user of a character limit for the "),o("code",null,"v-textarea"),a(". ")],-1),_e=o("p",null,[a("The "),o("code",null,"append-icon"),a(", "),o("code",null,"prepend-icon"),a(", "),o("code",null,"append-inner-icon"),a(" and "),o("code",null,"prepend-inner-icon"),a(" props help add context to v-textarea.")],-1),Ve=o("p",null,[a("The "),o("code",null,"rows"),a(" prop allows you to define how many rows the textarea has, when combined with the "),o("code",null,"row-height"),a(" prop you can further customize your rows by defining their height.")],-1),Te=o("p",null,[o("code",null,"v-textarea"),a("'s have the option to remain the same size regardless of their content's size, using the "),o("code",null,"no-resize"),a(" prop.")],-1),we=o("p",null,[a("Use "),o("code",null,"rules"),a(" prop to validate the textarea.")],-1),je={__name:"textarea",setup(p){return(r,u)=>{const s=Q,n=y,T=J,w=q,f=E,b=O,P=G,g=B,C=M,v=A,R=k,D=j;return d(),i(V,{class:"match-height"},{default:l(()=>[e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Basic",code:Z},{default:l(()=>[de,e(s)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Auto Grow",code:X},{default:l(()=>[ie,e(T)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12"},{default:l(()=>[e(n,{title:"Variant",code:se},{default:l(()=>[pe,e(w)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"States",code:ce},{default:l(()=>[ue,e(f)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Browser autocomplete",code:ee},{default:l(()=>[he,e(b)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Clearable",code:le},{default:l(()=>[me,e(P)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Counter",code:oe},{default:l(()=>[xe,e(g)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Icons",code:ae},{default:l(()=>[_e,e(C)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Rows",code:re},{default:l(()=>[Ve,e(v)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"No resize",code:te},{default:l(()=>[Te,e(R)]),_:1},8,["code"])]),_:1}),e(t,{cols:"12",md:"6"},{default:l(()=>[e(n,{title:"Validation",code:ne},{default:l(()=>[we,e(D)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{je as default};
