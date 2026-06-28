import{r as p,o as u,g as G,w as t,c as V,F as f,y as b,a as o,ac as s,d as c,x as m,ab as R,b as l,e as i,n as y,j as $,h as _}from"./index-HMn0UlBQ.js";import{_ as x}from"./AppCardCode-B6BaSGcr.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-kWWeRP1d.js";import"./VCardText-DUfc7MtM.js";const L={__name:"DemoRadioValidation",setup(v){const a=p(1),r=[d=>d!==3?!0:"Do not select the third one!"];return(d,e)=>(u(),G(R,{modelValue:c(a),"onUpdate:modelValue":e[0]||(e[0]=n=>m(a)?a.value=n:null),inline:"",rules:r},{default:t(()=>[(u(),V(f,null,b(3,n=>o(s,{key:n,error:c(a)===3,label:`Radio ${n}`,value:n},null,8,["error","label","value"])),64))]),_:1},8,["modelValue"]))}},w={__name:"DemoRadioIcon",setup(v){const a=p(1);return(r,d)=>(u(),G(R,{modelValue:c(a),"onUpdate:modelValue":d[0]||(d[0]=e=>m(a)?a.value=e:null),"false-icon":"ri-notification-off-line","true-icon":"ri-notification-3-line"},{default:t(()=>[(u(),V(f,null,b(2,e=>o(s,{key:e,label:`Radio ${e}`,value:e},null,8,["label","value"])),64))]),_:1},8,["modelValue"]))}},C=l("div",null,[i("Your favorite "),l("strong",null,"search engine")],-1),U=l("div",null,[i(" Of course it's "),l("span",{class:"text-success"}," Google ")],-1),S=l("div",null,[i(" Definitely "),l("span",{class:"text-primary"}," DuckDuckGo ")],-1),j={__name:"DemoRadioLabelSlot",setup(v){const a=p("DuckDuckGo");return(r,d)=>(u(),G(R,{modelValue:c(a),"onUpdate:modelValue":d[0]||(d[0]=e=>m(a)?a.value=e:null)},{label:t(()=>[C]),default:t(()=>[o(s,{value:"Google"},{label:t(()=>[U]),_:1}),o(s,{value:"DuckDuckGo"},{label:t(()=>[S]),_:1})]),_:1},8,["modelValue"]))}},I={__name:"DemoRadioDensity",setup(v){const a=p("radio-1"),r=p("radio-1");return(d,e)=>(u(),V(f,null,[o(R,{modelValue:c(a),"onUpdate:modelValue":e[0]||(e[0]=n=>m(a)?a.value=n:null)},{default:t(()=>[o(s,{label:"Option 1",value:"radio-1",density:"compact"}),o(s,{label:"Option 2",value:"radio-2",density:"compact"})]),_:1},8,["modelValue"]),o(y,{class:"my-3"}),o(R,{modelValue:c(r),"onUpdate:modelValue":e[1]||(e[1]=n=>m(r)?r.value=n:null),inline:""},{default:t(()=>[o(s,{label:"Option 1",value:"radio-1",density:"compact"}),o(s,{label:"Option 2",value:"radio-2",density:"compact"})]),_:1},8,["modelValue"])],64))}},B={__name:"DemoRadioInline",setup(v){const a=p("radio-1"),r=p("radio-1");return(d,e)=>(u(),V(f,null,[o(R,{modelValue:c(a),"onUpdate:modelValue":e[0]||(e[0]=n=>m(a)?a.value=n:null)},{default:t(()=>[o(s,{label:"Option 1",value:"radio-1"}),o(s,{label:"Option 2",value:"radio-2"})]),_:1},8,["modelValue"]),o(y,{class:"my-4"}),o(R,{modelValue:c(r),"onUpdate:modelValue":e[1]||(e[1]=n=>m(r)?r.value=n:null),inline:""},{default:t(()=>[o(s,{label:"Option 1",value:"radio-1"}),o(s,{label:"Option 2",value:"radio-2"})]),_:1},8,["modelValue"])],64))}},E={__name:"DemoRadioColors",setup(v){const a=p("primary"),r=["Primary","Secondary","Success","Info","Warning","Error"];return(d,e)=>(u(),G(R,{modelValue:c(a),"onUpdate:modelValue":e[0]||(e[0]=n=>m(a)?a.value=n:null),inline:""},{default:t(()=>[(u(),V(f,null,b(r,n=>o(s,{key:n,label:n,color:n.toLocaleLowerCase(),value:n.toLocaleLowerCase()},null,8,["label","color","value"])),64))]),_:1},8,["modelValue"]))}},A={class:""},F={__name:"DemoRadioBasic",setup(v){const a=p(1);return(r,d)=>(u(),V("div",A,[o(R,{modelValue:c(a),"onUpdate:modelValue":d[0]||(d[0]=e=>m(a)?a.value=e:null)},{default:t(()=>[(u(),V(f,null,b(2,e=>o(s,{key:e,label:`Radio ${e}`,value:e},null,8,["label","value"])),64))]),_:1},8,["modelValue"])]))}},N={ts:`<script lang="ts" setup>
const radioGroup = ref(1)
<\/script>

<template>
  <div class="">
    <VRadioGroup v-model="radioGroup">
      <VRadio
        v-for="n in 2"
        :key="n"
        :label="\`Radio \${n}\`"
        :value="n"
      />
    </VRadioGroup>
  </div>
</template>
`,js:`<script setup>
const radioGroup = ref(1)
<\/script>

<template>
  <div class="">
    <VRadioGroup v-model="radioGroup">
      <VRadio
        v-for="n in 2"
        :key="n"
        :label="\`Radio \${n}\`"
        :value="n"
      />
    </VRadioGroup>
  </div>
</template>
`},P={ts:`<script lang="ts" setup>
const selectedRadio = ref('primary')
const colorsRadio = ['Primary', 'Secondary', 'Success', 'Info', 'Warning', 'Error']
<\/script>

<template>
  <VRadioGroup
    v-model="selectedRadio"
    inline
  >
    <VRadio
      v-for="radio in colorsRadio"
      :key="radio"
      :label="radio"
      :color="radio.toLocaleLowerCase()"
      :value="radio.toLocaleLowerCase()"
    />
  </VRadioGroup>
</template>
`,js:`<script setup>
const selectedRadio = ref('primary')

const colorsRadio = [
  'Primary',
  'Secondary',
  'Success',
  'Info',
  'Warning',
  'Error',
]
<\/script>

<template>
  <VRadioGroup
    v-model="selectedRadio"
    inline
  >
    <VRadio
      v-for="radio in colorsRadio"
      :key="radio"
      :label="radio"
      :color="radio.toLocaleLowerCase()"
      :value="radio.toLocaleLowerCase()"
    />
  </VRadioGroup>
</template>
`},T={ts:`<script lang="ts" setup>
const columnRadio = ref('radio-1')
const inlineRadio = ref('radio-1')
<\/script>

<template>
  <VRadioGroup v-model="columnRadio">
    <VRadio
      label="Option 1"
      value="radio-1"
      density="compact"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
      density="compact"
    />
  </VRadioGroup>

  <VDivider class="my-3" />

  <VRadioGroup
    v-model="inlineRadio"
    inline
  >
    <VRadio
      label="Option 1"
      value="radio-1"
      density="compact"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
      density="compact"
    />
  </VRadioGroup>
</template>
`,js:`<script setup>
const columnRadio = ref('radio-1')
const inlineRadio = ref('radio-1')
<\/script>

<template>
  <VRadioGroup v-model="columnRadio">
    <VRadio
      label="Option 1"
      value="radio-1"
      density="compact"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
      density="compact"
    />
  </VRadioGroup>

  <VDivider class="my-3" />

  <VRadioGroup
    v-model="inlineRadio"
    inline
  >
    <VRadio
      label="Option 1"
      value="radio-1"
      density="compact"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
      density="compact"
    />
  </VRadioGroup>
</template>
`},W={ts:`<script lang="ts" setup>
const radioGroup = ref(1)
<\/script>

<template>
  <VRadioGroup
    v-model="radioGroup"
    false-icon="ri-notification-off-line"
    true-icon="ri-notification-3-line"
  >
    <VRadio
      v-for="n in 2"
      :key="n"
      :label="\`Radio \${n}\`"
      :value="n"
    />
  </VRadioGroup>
</template>
`,js:`<script setup>
const radioGroup = ref(1)
<\/script>

<template>
  <VRadioGroup
    v-model="radioGroup"
    false-icon="ri-notification-off-line"
    true-icon="ri-notification-3-line"
  >
    <VRadio
      v-for="n in 2"
      :key="n"
      :label="\`Radio \${n}\`"
      :value="n"
    />
  </VRadioGroup>
</template>
`},Y={ts:`<script lang="ts" setup>
const columnRadio = ref('radio-1')
const inlineRadio = ref('radio-1')
<\/script>

<template>
  <VRadioGroup v-model="columnRadio">
    <VRadio
      label="Option 1"
      value="radio-1"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
    />
  </VRadioGroup>

  <VDivider class="my-4" />

  <VRadioGroup
    v-model="inlineRadio"
    inline
  >
    <VRadio
      label="Option 1"
      value="radio-1"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
    />
  </VRadioGroup>
</template>
`,js:`<script setup>
const columnRadio = ref('radio-1')
const inlineRadio = ref('radio-1')
<\/script>

<template>
  <VRadioGroup v-model="columnRadio">
    <VRadio
      label="Option 1"
      value="radio-1"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
    />
  </VRadioGroup>

  <VDivider class="my-4" />

  <VRadioGroup
    v-model="inlineRadio"
    inline
  >
    <VRadio
      label="Option 1"
      value="radio-1"
    />
    <VRadio
      label="Option 2"
      value="radio-2"
    />
  </VRadioGroup>
</template>
`},H={ts:`<script lang="ts" setup>
const radios = ref('DuckDuckGo')
<\/script>

<template>
  <VRadioGroup v-model="radios">
    <template #label>
      <div>Your favorite <strong>search engine</strong></div>
    </template>

    <VRadio value="Google">
      <template #label>
        <div>
          Of course it's <span class="text-success">
            Google
          </span>
        </div>
      </template>
    </VRadio>

    <VRadio value="DuckDuckGo">
      <template #label>
        <div>
          Definitely <span class="text-primary">
            DuckDuckGo
          </span>
        </div>
      </template>
    </VRadio>
  </VRadioGroup>
</template>
`,js:`<script setup>
const radios = ref('DuckDuckGo')
<\/script>

<template>
  <VRadioGroup v-model="radios">
    <template #label>
      <div>Your favorite <strong>search engine</strong></div>
    </template>

    <VRadio value="Google">
      <template #label>
        <div>
          Of course it's <span class="text-success">
            Google
          </span>
        </div>
      </template>
    </VRadio>

    <VRadio value="DuckDuckGo">
      <template #label>
        <div>
          Definitely <span class="text-primary">
            DuckDuckGo
          </span>
        </div>
      </template>
    </VRadio>
  </VRadioGroup>
</template>
`},M={ts:`<script lang="ts" setup>
const radioGroup = ref(1)
const rules = [(value: number) => (value !== 3 ? true : 'Do not select the third one!')]
<\/script>

<template>
  <VRadioGroup
    v-model="radioGroup"
    inline
    :rules="rules"
  >
    <VRadio
      v-for="n in 3"
      :key="n"
      :error="radioGroup === 3 "
      :label="\`Radio \${n}\`"
      :value="n"
    />
  </VRadioGroup>
</template>
`,js:`<script setup>
const radioGroup = ref(1)
const rules = [value => value !== 3 ? true : 'Do not select the third one!']
<\/script>

<template>
  <VRadioGroup
    v-model="radioGroup"
    inline
    :rules="rules"
  >
    <VRadio
      v-for="n in 3"
      :key="n"
      :error="radioGroup === 3 "
      :label="\`Radio \${n}\`"
      :value="n"
    />
  </VRadioGroup>
</template>
`},q=l("p",null,[i("The "),l("code",null,"v-radio"),i(" component is a simple radio button.")],-1),z=l("p",null,[i("Radios can be colored by using any of the built-in colors and contextual names using the "),l("code",null,"color"),i(" prop.")],-1),J=l("p",null,[i("Use "),l("code",null,"inline"),i(" prop to displays radio buttons in row.")],-1),K=l("p",null,[i("Use "),l("code",null,"density"),i(" prop to adjusts the spacing within the component. Available options are: "),l("code",null,"default"),i(", "),l("code",null,"comfortable"),i(", and "),l("code",null,"compact"),i(".")],-1),Q=l("p",null,[i("Radio Group labels can be defined in "),l("code",null,"label"),i(" slot - that will allow to use HTML content.")],-1),X=l("p",null,[i("Use "),l("code",null,"false-icon"),i(" and "),l("code",null,"true-icon"),i(" prop to set icon on inactive and active state.")],-1),Z=l("p",null,[i("Use "),l("code",null,"rules"),i(" prop to validate a radio. Accepts a mixed array of types "),l("code",null,"function"),i(", "),l("code",null,"boolean"),i(" and "),l("code",null,"string"),i(". Functions pass an input value as an argument and must return either "),l("code",null,"true"),i(" / "),l("code",null,"false"),i(" or a string containing an error message.")],-1),io={__name:"radio",setup(v){return(a,r)=>{const d=F,e=x,n=E,D=B,g=I,k=j,O=w,h=L;return u(),G($,{class:"match-height"},{default:t(()=>[o(_,{cols:"12",md:"6"},{default:t(()=>[o(e,{title:"Basic",code:N},{default:t(()=>[q,o(d)]),_:1},8,["code"])]),_:1}),o(_,{cols:"12",md:"6"},{default:t(()=>[o(e,{title:"Colors",code:P},{default:t(()=>[z,o(n)]),_:1},8,["code"])]),_:1}),o(_,{cols:"12",md:"6"},{default:t(()=>[o(e,{title:"Inline",code:Y},{default:t(()=>[J,o(D)]),_:1},8,["code"])]),_:1}),o(_,{cols:"12",md:"6"},{default:t(()=>[o(e,{title:"Density",code:T},{default:t(()=>[K,o(g)]),_:1},8,["code"])]),_:1}),o(_,{cols:"12",md:"6"},{default:t(()=>[o(e,{title:"Label Slot",code:H},{default:t(()=>[Q,o(k)]),_:1},8,["code"])]),_:1}),o(_,{cols:"12",md:"6"},{default:t(()=>[o(e,{title:"Icon",code:W},{default:t(()=>[X,o(O)]),_:1},8,["code"])]),_:1}),o(_,{cols:"12",md:"6"},{default:t(()=>[o(e,{title:"Validation",code:M},{default:t(()=>[Z,o(h)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{io as default};
