import{V as A}from"./VNodeRenderer-CXOoVCZp.js";import{r as V,o as m,g as h,w as l,a as e,m as w,am as D,e as t,eM as U,d,C as q,aC as S,f as j,c as H,i as N,x,h as i,j as g,_ as T,b as o}from"./index-DTF6M3zX.js";import{V as L}from"./VTooltip-BNPRA7Rm.js";import{V as s}from"./VTextField-Bl4oYwB_.js";import{r as B,e as E}from"./validators-DD7Euqo8.js";import{V as W}from"./VForm-CQX2MNf3.js";import{_ as z}from"./AppCardCode-D9Q9Ghx4.js";/* empty css                   */import"./VCounter-BZjcQMzO.js";import"./VField-Dd4OVWbd.js";import"./helpers-cDhjtOJI.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-Dx0yDcdM.js";import"./VCardText-CdysxbXC.js";const Y={key:0,class:"ms-3"},O={__name:"DemoTextfieldIconSlots",setup(_){const n=V("Hey!"),r=V(!1),u=()=>{r.value=!0,n.value="Wait for it...",setTimeout(()=>{r.value=!1,n.value="You've clicked me!"},2e3)};return(a,f)=>(m(),h(s,{modelValue:d(n),"onUpdate:modelValue":f[0]||(f[0]=c=>x(n)?n.value=c:null),density:"compact",clearable:"","clear-icon":"ri-close-line",label:"Message",placeholder:"Hey!!",type:"text"},{prepend:l(()=>[e(L,{location:"bottom"},{activator:l(({props:c})=>[e(w,D(c,{icon:"ri-question-line"}),null,16)]),default:l(()=>[t(" I'm a tooltip ")]),_:1})]),"append-inner":l(()=>[e(U,{"leave-absolute":""},{default:l(()=>[d(r)?(m(),h(q,{key:0,size:"24",color:"info",indeterminate:""})):(m(),h(d(A),{key:1,nodes:d(S).app.logo},null,8,["nodes"]))]),_:1})]),append:l(()=>[e(j,{size:a.$vuetify.display.smAndDown?"small":"large",icon:a.$vuetify.display.smAndDown,onClick:u},{default:l(()=>[e(w,{icon:"ri-focus-3-line"}),a.$vuetify.display.mdAndUp?(m(),H("span",Y,"Click me")):N("",!0)]),_:1},8,["size","icon"])]),_:1},8,["modelValue"]))}},G={__name:"DemoTextfieldPasswordInput",setup(_){const n=V(!1),r=V(!0),u=V("Password"),a=V("wqfasds"),f={required:c=>!!c||"Required.",min:c=>c.length>=8||"Min 8 characters"};return(c,p)=>(m(),h(g,null,{default:l(()=>[e(i,{cols:"12",sm:"6"},{default:l(()=>[e(s,{modelValue:d(u),"onUpdate:modelValue":p[0]||(p[0]=b=>x(u)?u.value=b:null),"append-inner-icon":d(n)?"ri-eye-off-line":"ri-eye-line",rules:[f.required,f.min],type:d(n)?"text":"password",name:"input-10-1",label:"Normal with hint text",hint:"At least 8 characters",placeholder:"············",counter:"","onClick:appendInner":p[1]||(p[1]=b=>n.value=!d(n))},null,8,["modelValue","append-inner-icon","rules","type"])]),_:1}),e(i,{cols:"12",sm:"6"},{default:l(()=>[e(s,{modelValue:d(a),"onUpdate:modelValue":p[2]||(p[2]=b=>x(a)?a.value=b:null),rules:[f.required,f.min],"append-inner-icon":d(r)?"ri-eye-off-line":"ri-eye-line",type:d(r)?"text":"password",name:"input-10-2",placeholder:"············",label:"Visible",hint:"At least 8 characters","onClick:appendInner":p[3]||(p[3]=b=>r.value=!d(r))},null,8,["modelValue","rules","append-inner-icon","type"])]),_:1})]),_:1}))}},J={},K=o("strong",null,"icon",-1);function Q(_,n){return m(),h(s,{placeholder:"Placeholder Text"},{label:l(()=>[t(" What about  "),K,t(" here? "),e(w,{icon:"ri-file-search-line"})]),_:1})}const X=T(J,[["render",Q]]),Z={__name:"DemoTextfieldIconEvents",setup(_){const n=V("Hey!"),r=V(!0),u=V(0),a=()=>{r.value=!r.value},f=()=>{n.value=""},c=()=>{u.value=0},p=()=>{c(),f()};return(b,v)=>(m(),h(s,{modelValue:d(n),"onUpdate:modelValue":v[0]||(v[0]=C=>x(n)?n.value=C:null),clearable:"",type:"text",label:"Message",color:"primary",placeholder:"Hey!!","clear-icon":"ri-close-line","append-icon":d(n)?"ri-send-plane-2-line":"ri-mic-line","append-inner-icon":d(r)?"ri-pushpin-line":"ri-unpin-line","onClick:appendInner":a,"onClick:append":p,"onClick:clear":f},null,8,["modelValue","append-icon","append-inner-icon"]))}},ee={__name:"DemoTextfieldValidation",setup(_){const n=V("");return(r,u)=>(m(),h(W,null,{default:l(()=>[e(s,{modelValue:d(n),"onUpdate:modelValue":u[0]||(u[0]=a=>x(n)?n.value=a:null),rules:["requiredValidator"in r?r.requiredValidator:d(B),"emailValidator"in r?r.emailValidator:d(E)],placeholder:"johnedoe@email.com",label:"E-mail"},null,8,["modelValue","rules"])]),_:1}))}},le={};function oe(_,n){return m(),h(s,{label:"Regular",placeholder:"Placeholder Text","single-line":""})}const te=T(le,[["render",oe]]),ne={__name:"DemoTextfieldPrefixesAndSuffixes",setup(_){const n=V(10.05),r=V(28.02),u=V("example"),a=V("04:56");return(f,c)=>(m(),h(g,null,{default:l(()=>[e(i,{cols:"12"},{default:l(()=>[e(s,{modelValue:d(n),"onUpdate:modelValue":c[0]||(c[0]=p=>x(n)?n.value=p:null),label:"Amount",prefix:"$",type:"number",placeholder:"10.05"},null,8,["modelValue"])]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{modelValue:d(r),"onUpdate:modelValue":c[1]||(c[1]=p=>x(r)?r.value=p:null),label:"Weight",suffix:"lbs",type:"number",placeholder:"28.02"},null,8,["modelValue"])]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{modelValue:d(u),"onUpdate:modelValue":c[2]||(c[2]=p=>x(u)?u.value=p:null),label:"Email address",suffix:"@gmail.com",placeholder:"example"},null,8,["modelValue"])]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{modelValue:d(a),"onUpdate:modelValue":c[3]||(c[3]=p=>x(a)?a.value=p:null),label:"Label Text",type:"time",suffix:"PST",placeholder:"04:56"},null,8,["modelValue"])]),_:1})]),_:1}))}},ae={};function ie(_,n){return m(),h(g,null,{default:l(()=>[e(i,{cols:"12"},{default:l(()=>[e(s,{label:"Prepend","prepend-icon":"ri-map-pin-line",placeholder:"Placeholder Text"})]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{label:"Prepend Inner","prepend-inner-icon":"ri-map-pin-line",placeholder:"Placeholder Text"})]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{label:"Append","append-icon":"ri-map-pin-line",placeholder:"Placeholder Text"})]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{label:"Append Inner","append-inner-icon":"ri-map-pin-line",placeholder:"Placeholder Text"})]),_:1})]),_:1})}const se=T(ae,[["render",ie]]),re={};function ce(_,n){return m(),h(s,{color:"success",label:"First name",placeholder:"Placeholder Text"})}const de=T(re,[["render",ce]]),pe={};function ue(_,n){return m(),h(s,{placeholder:"Placeholder Text",label:"Regular",clearable:""})}const me=T(pe,[["render",ue]]),fe={__name:"DemoTextfieldCounter",setup(_){const n=V("Preliminary report"),r=V("California is a state in the western United States"),u=[a=>a.length<=25||"Max 25 characters"];return(a,f)=>(m(),h(g,null,{default:l(()=>[e(i,{cols:"12"},{default:l(()=>[e(s,{modelValue:d(n),"onUpdate:modelValue":f[0]||(f[0]=c=>x(n)?n.value=c:null),rules:u,counter:"25",placeholder:"Placeholder Text",hint:"This field uses counter prop",label:"Regular"},null,8,["modelValue"])]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{modelValue:d(r),"onUpdate:modelValue":f[1]||(f[1]=c=>x(r)?r.value=c:null),rules:u,counter:"",maxlength:"25",placeholder:"Placeholder Text",hint:"This field uses maxlength attribute",label:"Limit exceeded"},null,8,["modelValue"])]),_:1})]),_:1}))}},he={};function Ve(_,n){return m(),h(g,null,{default:l(()=>[e(i,null,{default:l(()=>[e(s,{label:"Disabled",placeholder:"Placeholder Text",disabled:""})]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(s,{placeholder:"Placeholder Text",label:"Readonly",readonly:""})]),_:1})]),_:1})}const _e=T(he,[["render",Ve]]),xe={};function Te(_,n){return m(),h(g,null,{default:l(()=>[e(i,{cols:"12",md:"6"},{default:l(()=>[e(s,{label:"Outlined",placeholder:"Placeholder Text"})]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(s,{label:"Filled",variant:"filled",placeholder:"Placeholder Text"})]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(s,{label:"Solo",variant:"solo",placeholder:"Placeholder Text"})]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(s,{label:"Plain",variant:"plain",placeholder:"Placeholder Text"})]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(s,{label:"Underlined",variant:"underlined",placeholder:"Placeholder Text"})]),_:1})]),_:1})}const be=T(xe,[["render",Te]]),ge={};function ve(_,n){return m(),h(s,{label:"Compact",density:"compact",placeholder:"Placeholder Text"})}const Ce=T(ge,[["render",ve]]),we={};function ye(_,n){return m(),h(s,{label:"Regular",placeholder:"Placeholder Text"})}const Pe=T(we,[["render",ye]]),Fe={ts:`<template>
  <VTextField
    label="Regular"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <VTextField
    label="Regular"
    placeholder="Placeholder Text"
  />
</template>
`},ke={ts:`<template>
  <VTextField
    placeholder="Placeholder Text"
    label="Regular"
    clearable
  />
</template>
`,js:`<template>
  <VTextField
    placeholder="Placeholder Text"
    label="Regular"
    clearable
  />
</template>
`},Re={ts:`<script lang="ts" setup>
const title = ref('Preliminary report')
const description = ref('California is a state in the western United States')
const rules = [(v: string) => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VTextField
        v-model="title"
        :rules="rules"
        counter="25"
        placeholder="Placeholder Text"
        hint="This field uses counter prop"
        label="Regular"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="description"
        :rules="rules"
        counter
        maxlength="25"
        placeholder="Placeholder Text"
        hint="This field uses maxlength attribute"
        label="Limit exceeded"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const title = ref('Preliminary report')
const description = ref('California is a state in the western United States')
const rules = [v => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VTextField
        v-model="title"
        :rules="rules"
        counter="25"
        placeholder="Placeholder Text"
        hint="This field uses counter prop"
        label="Regular"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="description"
        :rules="rules"
        counter
        maxlength="25"
        placeholder="Placeholder Text"
        hint="This field uses maxlength attribute"
        label="Limit exceeded"
      />
    </VCol>
  </VRow>
</template>
`},Ie={ts:`<template>
  <VTextField
    color="success"
    label="First name"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <VTextField
    color="success"
    label="First name"
    placeholder="Placeholder Text"
  />
</template>
`},$e={ts:`<template>
  <VTextField
    label="Compact"
    density="compact"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <VTextField
    label="Compact"
    density="compact"
    placeholder="Placeholder Text"
  />
</template>
`},Me={ts:`<script lang="ts" setup>
const message = ref('Hey!')
const marker = ref(true)
const iconIndex = ref(0)

const toggleMarker = () => {
  marker.value = !marker.value
}

const clearMessage = () => {
  message.value = ''
}

const resetIcon = () => {
  iconIndex.value = 0
}

const sendMessage = () => {
  resetIcon()
  clearMessage()
}
<\/script>

<template>
  <VTextField
    v-model="message"
    clearable
    type="text"
    label="Message"
    color="primary"
    placeholder="Hey!!"
    clear-icon="ri-close-line"
    :append-icon="message ? 'ri-send-plane-2-line' : 'ri-mic-line'"
    :append-inner-icon="marker ? 'ri-pushpin-line' : 'ri-unpin-line'"
    @click:append-inner="toggleMarker"
    @click:append="sendMessage"
    @click:clear="clearMessage"
  />
</template>
`,js:`<script setup>
const message = ref('Hey!')
const marker = ref(true)
const iconIndex = ref(0)

const toggleMarker = () => {
  marker.value = !marker.value
}

const clearMessage = () => {
  message.value = ''
}

const resetIcon = () => {
  iconIndex.value = 0
}

const sendMessage = () => {
  resetIcon()
  clearMessage()
}
<\/script>

<template>
  <VTextField
    v-model="message"
    clearable
    type="text"
    label="Message"
    color="primary"
    placeholder="Hey!!"
    clear-icon="ri-close-line"
    :append-icon="message ? 'ri-send-plane-2-line' : 'ri-mic-line'"
    :append-inner-icon="marker ? 'ri-pushpin-line' : 'ri-unpin-line'"
    @click:append-inner="toggleMarker"
    @click:append="sendMessage"
    @click:clear="clearMessage"
  />
</template>
`},Ae={ts:`<script lang="ts" setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const message = ref('Hey!')
const loading = ref(false)

const clickMe = () => {
  loading.value = true
  message.value = 'Wait for it...'

  setTimeout(() => {
    loading.value = false
    message.value = 'You've clicked me!'
  }, 2000)
}
<\/script>

<template>
  <VTextField
    v-model="message"
    density="compact"
    clearable
    clear-icon="ri-close-line"
    label="Message"
    placeholder="Hey!!"
    type="text"
  >
    <!-- Prepend -->
    <template #prepend>
      <VTooltip location="bottom">
        <template #activator="{ props }">
          <VIcon
            v-bind="props"
            icon="ri-question-line"
          />
        </template>
        I'm a tooltip
      </VTooltip>
    </template>

    <!-- AppendInner -->
    <template #append-inner>
      <VFadeTransition leave-absolute>
        <VProgressCircular
          v-if="loading"
          size="24"
          color="info"
          indeterminate
        />

        <VNodeRenderer
          v-else
          :nodes="themeConfig.app.logo"
        />
      </VFadeTransition>
    </template>

    <!-- Append -->
    <template #append>
      <VBtn
        :size="$vuetify.display.smAndDown ? 'small' : 'large'"
        :icon="$vuetify.display.smAndDown"
        @click="clickMe"
      >
        <VIcon icon="ri-focus-3-line" />
        <span
          v-if="$vuetify.display.mdAndUp"
          class="ms-3"
        >Click me</span>
      </VBtn>
    </template>
  </VTextField>
</template>
`,js:`<script setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const message = ref('Hey!')
const loading = ref(false)

const clickMe = () => {
  loading.value = true
  message.value = 'Wait for it...'
  setTimeout(() => {
    loading.value = false
    message.value = 'You've clicked me!'
  }, 2000)
}
<\/script>

<template>
  <VTextField
    v-model="message"
    density="compact"
    clearable
    clear-icon="ri-close-line"
    label="Message"
    placeholder="Hey!!"
    type="text"
  >
    <!-- Prepend -->
    <template #prepend>
      <VTooltip location="bottom">
        <template #activator="{ props }">
          <VIcon
            v-bind="props"
            icon="ri-question-line"
          />
        </template>
        I'm a tooltip
      </VTooltip>
    </template>

    <!-- AppendInner -->
    <template #append-inner>
      <VFadeTransition leave-absolute>
        <VProgressCircular
          v-if="loading"
          size="24"
          color="info"
          indeterminate
        />

        <VNodeRenderer
          v-else
          :nodes="themeConfig.app.logo"
        />
      </VFadeTransition>
    </template>

    <!-- Append -->
    <template #append>
      <VBtn
        :size="$vuetify.display.smAndDown ? 'small' : 'large'"
        :icon="$vuetify.display.smAndDown"
        @click="clickMe"
      >
        <VIcon icon="ri-focus-3-line" />
        <span
          v-if="$vuetify.display.mdAndUp"
          class="ms-3"
        >Click me</span>
      </VBtn>
    </template>
  </VTextField>
</template>
`},De={ts:`<template>
  <VRow>
    <VCol cols="12">
      <VTextField
        label="Prepend"
        prepend-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        label="Prepend Inner"
        prepend-inner-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        label="Append"
        append-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        label="Append Inner"
        append-inner-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol cols="12">
      <VTextField
        label="Prepend"
        prepend-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        label="Prepend Inner"
        prepend-inner-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        label="Append"
        append-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        label="Append Inner"
        append-inner-icon="ri-map-pin-line"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`},Ue={ts:`<template>
  <VTextField placeholder="Placeholder Text">
    <template #label>
      What about &nbsp;<strong>icon</strong>&nbsp;here?
      <VIcon icon="ri-file-search-line" />
    </template>
  </VTextField>
</template>
`,js:`<template>
  <VTextField placeholder="Placeholder Text">
    <template #label>
      What about &nbsp;<strong>icon</strong>&nbsp;here?
      <VIcon icon="ri-file-search-line" />
    </template>
  </VTextField>
</template>
`},qe={ts:`<script lang="ts" setup>
const show1 = ref(false)
const show2 = ref(true)
const password = ref('Password')
const confirmPassword = ref('wqfasds')

const rules = {
  required: (value: string) => !!value || 'Required.',
  min: (v: string) => v.length >= 8 || 'Min 8 characters',
}
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextField
        v-model="password"
        :append-inner-icon="show1 ? 'ri-eye-off-line' : 'ri-eye-line'"
        :rules="[rules.required, rules.min]"
        :type="show1 ? 'text' : 'password'"
        name="input-10-1"
        label="Normal with hint text"
        hint="At least 8 characters"
        placeholder="············"
        counter
        @click:append-inner="show1 = !show1"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextField
        v-model="confirmPassword"
        :rules="[rules.required, rules.min]"
        :append-inner-icon="show2 ? 'ri-eye-off-line' : 'ri-eye-line'"
        :type="show2 ? 'text' : 'password'"
        name="input-10-2"
        placeholder="············"
        label="Visible"
        hint="At least 8 characters"
        @click:append-inner="show2 = !show2"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const show1 = ref(false)
const show2 = ref(true)
const password = ref('Password')
const confirmPassword = ref('wqfasds')

const rules = {
  required: value => !!value || 'Required.',
  min: v => v.length >= 8 || 'Min 8 characters',
}
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextField
        v-model="password"
        :append-inner-icon="show1 ? 'ri-eye-off-line' : 'ri-eye-line'"
        :rules="[rules.required, rules.min]"
        :type="show1 ? 'text' : 'password'"
        name="input-10-1"
        label="Normal with hint text"
        hint="At least 8 characters"
        placeholder="············"
        counter
        @click:append-inner="show1 = !show1"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextField
        v-model="confirmPassword"
        :rules="[rules.required, rules.min]"
        :append-inner-icon="show2 ? 'ri-eye-off-line' : 'ri-eye-line'"
        :type="show2 ? 'text' : 'password'"
        name="input-10-2"
        placeholder="············"
        label="Visible"
        hint="At least 8 characters"
        @click:append-inner="show2 = !show2"
      />
    </VCol>
  </VRow>
</template>
`},Se={ts:`<script setup lang="ts">
const amount = ref(10.05)
const weight = ref(28.02)
const email = ref('example')
const time = ref('04:56')
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VTextField
        v-model="amount"
        label="Amount"
        prefix="$"
        type="number"
        placeholder="10.05"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="weight"
        label="Weight"
        suffix="lbs"
        type="number"
        placeholder="28.02"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="email"
        label="Email address"
        suffix="@gmail.com"
        placeholder="example"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="time"
        label="Label Text"
        type="time"
        suffix="PST"
        placeholder="04:56"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const amount = ref(10.05)
const weight = ref(28.02)
const email = ref('example')
const time = ref('04:56')
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VTextField
        v-model="amount"
        label="Amount"
        prefix="$"
        type="number"
        placeholder="10.05"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="weight"
        label="Weight"
        suffix="lbs"
        type="number"
        placeholder="28.02"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="email"
        label="Email address"
        suffix="@gmail.com"
        placeholder="example"
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        v-model="time"
        label="Label Text"
        type="time"
        suffix="PST"
        placeholder="04:56"
      />
    </VCol>
  </VRow>
</template>
`},je={ts:`<template>
  <VTextField
    label="Regular"
    placeholder="Placeholder Text"
    single-line
  />
</template>
`,js:`<template>
  <VTextField
    label="Regular"
    placeholder="Placeholder Text"
    single-line
  />
</template>
`},He={ts:`<template>
  <VRow>
    <VCol>
      <VTextField
        label="Disabled"
        placeholder="Placeholder Text"
        disabled
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        placeholder="Placeholder Text"
        label="Readonly"
        readonly
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol>
      <VTextField
        label="Disabled"
        placeholder="Placeholder Text"
        disabled
      />
    </VCol>

    <VCol cols="12">
      <VTextField
        placeholder="Placeholder Text"
        label="Readonly"
        readonly
      />
    </VCol>
  </VRow>
</template>
`},Ne={ts:`<script lang="ts" setup>
const email = ref('')
<\/script>

<template>
  <VForm>
    <VTextField
      v-model="email"
      :rules="[requiredValidator, emailValidator]"
      placeholder="johnedoe@email.com"
      label="E-mail"
    />
  </VForm>
</template>
`,js:`<script setup>
const email = ref('')
<\/script>

<template>
  <VForm>
    <VTextField
      v-model="email"
      :rules="[requiredValidator, emailValidator]"
      placeholder="johnedoe@email.com"
      label="E-mail"
    />
  </VForm>
</template>
`},Le={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Outlined"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Filled"
        variant="filled"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Solo"
        variant="solo"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Plain"
        variant="plain"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Underlined"
        variant="underlined"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Outlined"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Filled"
        variant="filled"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Solo"
        variant="solo"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Plain"
        variant="plain"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Underlined"
        variant="underlined"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`},Be=o("p",null,"Text fields components are used for collecting user provided information.",-1),Ee=o("p",null,[t("The "),o("code",null,"density"),t(" prop decreases the height of the text field based upon 1 of 3 levels of density; "),o("code",null,"default"),t(", "),o("code",null,"comfortable"),t(", and "),o("code",null,"compact"),t(".")],-1),We=o("p",null,[t("Use "),o("code",null,"solo"),t(", "),o("code",null,"filled"),t(", "),o("code",null,"outlined"),t(", "),o("code",null,"plain"),t(" and "),o("code",null,"underlined"),t(" option of "),o("code",null,"variant"),t(" prop to change the look of the textfield. ")],-1),ze=o("p",null,"Text fields can be disabled or readonly.",-1),Ye=o("p",null,[t("Use a "),o("code",null,"counter"),t(" prop to inform a user of the character limit.")],-1),Oe=o("p",null,"When clearable, you can customize the clear icon with clear-icon.",-1),Ge=o("p",null,[t("Use "),o("code",null,"color"),t(" prop to change the input border color.")],-1),Je=o("p",null,[t("You can add icons to the text field with "),o("code",null,"prepend-icon"),t(", "),o("code",null,"append-icon"),t(" and "),o("code",null,"append-inner-icon"),t(" and "),o("code",null,"prepend-inner-icon"),t(" props.")],-1),Ke=o("p",null,[t("The "),o("code",null,"prefix"),t(" and "),o("code",null,"suffix"),t(" properties allows you to prepend and append inline non-modifiable text next to the text field.")],-1),Qe=o("p",null,[o("code",null,"single-line"),t(" text fields do not float their label on focus or with data.")],-1),Xe=o("p",null,[t("Vuetify includes simple validation through the "),o("code",null,"rules"),t(" prop.")],-1),Ze=o("p",null,[o("code",null,"click:prepend"),t(", "),o("code",null,"click:append"),t(", "),o("code",null,"click:append-inner"),t(", and "),o("code",null,"click:clear"),t(" will be emitted when you click on the respective icon")],-1),el=o("p",null,[t("Text field label can be defined in "),o("code",null,"label"),t(" slot - that will allow to use HTML content.")],-1),ll=o("p",null,[t("Using the HTML input "),o("code",null,"type"),t(" password can be used with an appended icon and callback to control the visibility.")],-1),ol=o("p",null,[t("Instead of using "),o("code",null,"prepend"),t("/"),o("code",null,"append"),t("/"),o("code",null,"append-inner"),t(" icons you can use slots to extend input's functionality.")],-1),_l={__name:"textfield",setup(_){return(n,r)=>{const u=Pe,a=z,f=Ce,c=be,p=_e,b=fe,v=me,C=de,y=se,P=ne,F=te,k=ee,R=Z,I=X,$=G,M=O;return m(),h(g,{class:"match-height"},{default:l(()=>[e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Basic",code:Fe},{default:l(()=>[Be,e(u)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Density",code:$e},{default:l(()=>[Ee,e(f)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12"},{default:l(()=>[e(a,{title:"Variant",code:Le},{default:l(()=>[We,e(c)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"State",code:He},{default:l(()=>[ze,e(p)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Counter",code:Re},{default:l(()=>[Ye,e(b)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Clearable",code:ke},{default:l(()=>[Oe,e(v)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Custom Colors",code:Ie},{default:l(()=>[Ge,e(C)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Icons",code:De},{default:l(()=>[Je,e(y)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Prefixes and suffixes",code:Se},{default:l(()=>[Ke,e(P)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Single line",code:je},{default:l(()=>[Qe,e(F)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Validation",code:Ne},{default:l(()=>[Xe,e(k)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Icon events",code:Me},{default:l(()=>[Ze,e(R)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Label Slot",code:Ue},{default:l(()=>[el,e(I)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Password input",code:qe},{default:l(()=>[ll,e($)]),_:1},8,["code"])]),_:1}),e(i,{cols:"12",md:"6"},{default:l(()=>[e(a,{title:"Icon slots",code:Ae},{default:l(()=>[ol,e(M)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{_l as default};
