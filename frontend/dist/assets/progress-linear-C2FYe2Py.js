import{_ as f,o as i,c as g,a as e,K as l,r as v,d as _,x as h,w as t,b as o,t as P,g as L,R as k,S as B,M as $,j as M,h as p,e as r}from"./index-DTF6M3zX.js";import{_ as x}from"./AppCardCode-D9Q9Ghx4.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-Dx0yDcdM.js";import"./VCardText-CdysxbXC.js";const I={},D={class:"demo-space-y"};function U(m,s){return i(),g("div",D,[e(l,{"model-value":"75",striped:""}),e(l,{color:"success","model-value":"55",striped:""}),e(l,{color:"warning","model-value":"35",striped:""})])}const j=f(I,[["render",U]]),R={class:"demo-space-y"},S={__name:"DemoProgressLinearSlots",setup(m){const s=v(20),d=v(33),u=v(78);return(n,c)=>(i(),g("div",R,[e(l,{modelValue:_(u),"onUpdate:modelValue":c[0]||(c[0]=a=>h(u)?u.value=a:null),height:"8"},null,8,["modelValue"]),e(l,{modelValue:_(s),"onUpdate:modelValue":c[1]||(c[1]=a=>h(s)?s.value=a:null),height:"20"},{default:t(({value:a})=>[o("span",null,P(Math.ceil(a))+"%",1)]),_:1},8,["modelValue"]),e(l,{modelValue:_(d),"onUpdate:modelValue":c[2]||(c[2]=a=>h(d)?d.value=a:null),height:"20"},{default:t(()=>[o("span",null,P(Math.ceil(_(d)))+"%",1)]),_:1},8,["modelValue"])]))}},T={},C={class:"demo-space-y"};function N(m,s){return i(),g("div",C,[e(l,{"model-value":"78",height:"8",rounded:""}),e(l,{"model-value":"20",height:"20",rounded:""}),e(l,{"model-value":"33",height:"20",rounded:""})])}const A=f(T,[["render",N]]),E={};function K(m,s){return i(),L(l,{indeterminate:"",reverse:""})}const Y=f(E,[["render",K]]),q={};function z(m,s){return i(),L(l,{indeterminate:""})}const F=f(q,[["render",z]]),G={class:"demo-space-y"},H={__name:"DemoProgressLinearBuffering",setup(m){const s=v(10),d=v(20),u=v(),n=()=>{clearInterval(u.value),u.value=setInterval(()=>{s.value+=Math.random()*10+5,d.value+=Math.random()*10+6},2e3)};return k(n),B(()=>{clearInterval(u.value)}),$(s,()=>{if(s.value<100)return!1;s.value=0,d.value=10,n()}),(c,a)=>(i(),g("div",G,[e(l,{modelValue:_(s),"onUpdate:modelValue":a[0]||(a[0]=V=>h(s)?s.value=V:null),height:"8","buffer-value":_(d)},null,8,["modelValue","buffer-value"])]))}},J={},O={class:"demo-space-y"};function Q(m,s){return i(),g("div",O,[e(l,{"model-value":"15"}),e(l,{"model-value":"30",color:"secondary"}),e(l,{"model-value":"45",color:"success"})])}const W=f(J,[["render",Q]]),X={ts:`<script setup lang="ts">
const modelValue = ref(10)
const bufferValue = ref(20)
const interval = ref()

const startBuffer = () => {
  clearInterval(interval.value)

  interval.value = setInterval(() => {
    modelValue.value += Math.random() * (15 - 5) + 5
    bufferValue.value += Math.random() * (15 - 5) + 6
  }, 2000)
}

onMounted(startBuffer)

onBeforeUnmount(() => {
  clearInterval(interval.value)
})

watch(modelValue, () => {
  if (modelValue.value < 100)
    return false

  modelValue.value = 0
  bufferValue.value = 10
  startBuffer()
})
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="modelValue"
      height="8"
      :buffer-value="bufferValue"
    />
  </div>
</template>
`,js:`<script setup>
const modelValue = ref(10)
const bufferValue = ref(20)
const interval = ref()

const startBuffer = () => {
  clearInterval(interval.value)
  interval.value = setInterval(() => {
    modelValue.value += Math.random() * (15 - 5) + 5
    bufferValue.value += Math.random() * (15 - 5) + 6
  }, 2000)
}

onMounted(startBuffer)
onBeforeUnmount(() => {
  clearInterval(interval.value)
})
watch(modelValue, () => {
  if (modelValue.value < 100)
    return false
  modelValue.value = 0
  bufferValue.value = 10
  startBuffer()
})
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="modelValue"
      height="8"
      :buffer-value="bufferValue"
    />
  </div>
</template>
`},Z={ts:`<template>
  <div class="demo-space-y">
    <VProgressLinear model-value="15" />

    <VProgressLinear
      model-value="30"
      color="secondary"
    />

    <VProgressLinear
      model-value="45"
      color="success"
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-y">
    <VProgressLinear model-value="15" />

    <VProgressLinear
      model-value="30"
      color="secondary"
    />

    <VProgressLinear
      model-value="45"
      color="success"
    />
  </div>
</template>
`},ee={ts:`<template>
  <VProgressLinear indeterminate />
</template>
`,js:`<template>
  <VProgressLinear indeterminate />
</template>
`},oe={ts:`<template>
  <VProgressLinear
    indeterminate
    reverse
  />
</template>
`,js:`<template>
  <VProgressLinear
    indeterminate
    reverse
  />
</template>
`},se={ts:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="78"
      height="8"
      rounded
    />

    <VProgressLinear
      model-value="20"
      height="20"
      rounded
    />

    <VProgressLinear
      model-value="33"
      height="20"
      rounded
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="78"
      height="8"
      rounded
    />

    <VProgressLinear
      model-value="20"
      height="20"
      rounded
    />

    <VProgressLinear
      model-value="33"
      height="20"
      rounded
    />
  </div>
</template>
`},te={ts:`<script setup lang="ts">
const skill = ref(20)
const knowledge = ref(33)
const power = ref(78)
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="power"
      height="8"
    />

    <VProgressLinear
      v-model="skill"
      height="20"
    >
      <template #default="{ value }">
        <span>{{ Math.ceil(value) }}%</span>
      </template>
    </VProgressLinear>

    <VProgressLinear
      v-model="knowledge"
      height="20"
    >
      <span>{{ Math.ceil(knowledge) }}%</span>
    </VProgressLinear>
  </div>
</template>
`,js:`<script setup>
const skill = ref(20)
const knowledge = ref(33)
const power = ref(78)
<\/script>

<template>
  <div class="demo-space-y">
    <VProgressLinear
      v-model="power"
      height="8"
    />

    <VProgressLinear
      v-model="skill"
      height="20"
    >
      <template #default="{ value }">
        <span>{{ Math.ceil(value) }}%</span>
      </template>
    </VProgressLinear>

    <VProgressLinear
      v-model="knowledge"
      height="20"
    >
      <span>{{ Math.ceil(knowledge) }}%</span>
    </VProgressLinear>
  </div>
</template>
`},re={ts:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="75"
      striped
    />

    <VProgressLinear
      color="success"
      model-value="55"
      striped
    />

    <VProgressLinear
      color="warning"
      model-value="35"
      striped
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-y">
    <VProgressLinear
      model-value="75"
      striped
    />

    <VProgressLinear
      color="success"
      model-value="55"
      striped
    />

    <VProgressLinear
      color="warning"
      model-value="35"
      striped
    />
  </div>
</template>
`},le=o("p",null,[r("Use the props "),o("code",null,"color"),r(" and "),o("code",null,"background-color"),r(" to set colors.")],-1),ae=o("p",null,[r("The primary value is controlled by "),o("code",null,"v-model"),r(", whereas the buffer is controlled by the "),o("code",null,"buffer-value"),r(" prop.")],-1),ne=o("p",null,[r("for continuously animating progress bar,use prop "),o("code",null,"indeterminate"),r(". This indicates continuous process. ")],-1),de=o("p",null,[r("Use prop "),o("code",null,"reverse"),r(" to animate continuously in reverse direction. The component also has RTL support.")],-1),ue=o("p",null,[r("The "),o("code",null," rounded "),r(" prop is used to apply a border radius to the v-progress-linear component.")],-1),ce=o("p",null,[r("we can bind user input using "),o("code",null,"v-model"),r(".You can also use the default slot for the same.")],-1),ie=o("p",null,[r(" The "),o("code",null,"striped"),r(" prop is used to apply striped background.")],-1),ge={__name:"progress-linear",setup(m){return(s,d)=>{const u=W,n=x,c=H,a=F,V=Y,y=A,b=S,w=j;return i(),L(M,{class:"match-height"},{default:t(()=>[e(p,{cols:"12",md:"6"},{default:t(()=>[e(n,{title:"Color",code:Z},{default:t(()=>[le,e(u)]),_:1},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:t(()=>[e(n,{title:"Buffering",code:X},{default:t(()=>[ae,e(c)]),_:1},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:t(()=>[e(n,{title:"Indeterminate",code:ee},{default:t(()=>[ne,e(a)]),_:1},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:t(()=>[e(n,{title:"Reversed",code:oe},{default:t(()=>[de,e(V)]),_:1},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:t(()=>[e(n,{title:"Rounded",code:se},{default:t(()=>[ue,e(y)]),_:1},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:t(()=>[e(n,{title:"Slots",code:te},{default:t(()=>[ce,e(b)]),_:1},8,["code"])]),_:1}),e(p,{cols:"12",md:"6"},{default:t(()=>[e(n,{title:"Striped",code:re},{default:t(()=>[ie,e(w)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{ge as default};
