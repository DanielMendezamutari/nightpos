import{r as g,o as s,g as f,w as n,a as t,f as e,d as p,x as C,es as W,_ as d,c as u,e as o,m as B,b as a,A as D,B as z,h as i,j as x}from"./index-BQ1dVA52.js";import{_ as N}from"./AppCardCode-D9fjBC7F.js";import{V as v}from"./VAlert-BFCtr8xm.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-CTM8BfoQ.js";import"./VCardText-BrWzDuVo.js";const R={__name:"DemoButtonGroup",setup(c){const r=g(1);return(V,_)=>(s(),f(W,{modelValue:p(r),"onUpdate:modelValue":_[0]||(_[0]=l=>C(r)?r.value=l:null),density:"compact"},{default:n(()=>[t(e,{icon:"ri-align-left"}),t(e,{icon:"ri-align-center"}),t(e,{icon:"ri-align-right"}),t(e,{icon:"ri-align-justify"})]),_:1},8,["modelValue"]))}},U={},O={class:"demo-space-x"};function A(c,r){return s(),u("div",O,[t(e,{href:"https://pixinvent.com/"},{default:n(()=>[o(" String Literal ")]),_:1}),t(e,{href:"https://pixinvent.com/",target:"_blank",rel:"noopener noreferrer"},{default:n(()=>[o(" Open New Tab ")]),_:1})])}const q=d(U,[["render",A]]),G={},Q={class:"demo-space-x"};function Y(c,r){return s(),u("div",Q,[t(e,{to:"/components/alert"},{default:n(()=>[o(" String Literal ")]),_:1}),t(e,{color:"warning",to:{path:"/components/alert"}},{default:n(()=>[o(" Object Path ")]),_:1}),t(e,{color:"success",to:{name:"components-alert"}},{default:n(()=>[o(" Named Router ")]),_:1}),t(e,{color:"secondary",to:{path:"/components/alert",query:{plan:"private"}}},{default:n(()=>[o(" With Query ")]),_:1})])}const F=d(G,[["render",Y]]),H=c=>(D("data-v-c9540d41"),c=c(),z(),c),J={class:"demo-space-x"},K=H(()=>a("span",null,"Loading...",-1)),M={class:"custom-loader"},X={__name:"DemoButtonLoaders",setup(c){const r=g([]),V=_=>{r.value[_]=!0,setTimeout(()=>{r.value[_]=!1},3e3)};return(_,l)=>(s(),u("div",J,[t(e,{loading:p(r)[0],disabled:p(r)[0],color:"primary",onClick:l[0]||(l[0]=m=>V(0))},{default:n(()=>[o(" Accept Terms ")]),_:1},8,["loading","disabled"]),t(e,{loading:p(r)[1],disabled:p(r)[1],color:"secondary",onClick:l[1]||(l[1]=m=>V(1))},{default:n(()=>[o(" Upload "),t(B,{end:"",icon:"ri-upload-cloud-line"})]),_:1},8,["loading","disabled"]),t(e,{loading:p(r)[2],disabled:p(r)[2],color:"success",onClick:l[2]||(l[2]=m=>V(2))},{loader:n(()=>[K]),default:n(()=>[o(" Loader slot ")]),_:1},8,["loading","disabled"]),t(e,{loading:p(r)[3],disabled:p(r)[3],color:"info",onClick:l[3]||(l[3]=m=>V(3))},{loader:n(()=>[a("span",M,[t(B,{icon:"ri-refresh-line"})])]),default:n(()=>[o(" Icon Loader ")]),_:1},8,["loading","disabled"]),t(e,{loading:p(r)[4],disabled:p(r)[4],color:"warning",icon:"ri-upload-cloud-line",onClick:l[4]||(l[4]=m=>V(4))},null,8,["loading","disabled"])]))}},Z=d(X,[["__scopeId","data-v-c9540d41"]]),tt={};function nt(c,r){return s(),f(x,null,{default:n(()=>[t(i,{cols:"12",sm:"6"},{default:n(()=>[t(e,{block:""},{default:n(()=>[o(" Block Button ")]),_:1})]),_:1}),t(i,{cols:"12",sm:"6"},{default:n(()=>[t(e,{variant:"outlined",block:""},{default:n(()=>[o(" Block Button ")]),_:1})]),_:1})]),_:1})}const ot=d(tt,[["render",nt]]),et={},at={class:"demo-space-x"};function rt(c,r){return s(),u("div",at,[t(e,{size:"x-large"},{default:n(()=>[o(" Extra large Button ")]),_:1}),t(e,{color:"success",size:"large"},{default:n(()=>[o(" Large Button ")]),_:1}),t(e,{color:"info"},{default:n(()=>[o(" Normal Button ")]),_:1}),t(e,{size:"small",color:"warning"},{default:n(()=>[o(" Small Button ")]),_:1}),t(e,{size:"x-small",color:"error"},{default:n(()=>[o(" Extra small Button ")]),_:1})])}const lt=d(et,[["render",rt]]),ct={},it={class:"demo-space-x"};function st(c,r){return s(),u("div",it,[t(e,{icon:"ri-suitcase-line",variant:"text"}),t(e,{variant:"text",icon:"ri-user-add-line",color:"secondary"}),t(e,{icon:"ri-search-line",variant:"text",color:"success"}),t(e,{icon:"ri-thumb-up-line",variant:"text",color:"info"}),t(e,{icon:"ri-star-line",variant:"text",color:"warning"}),t(e,{icon:"ri-heart-line",variant:"text",color:"error"})])}const dt=d(ct,[["render",st]]),ut={},pt={class:"demo-space-x"};function _t(c,r){return s(),u("div",pt,[t(e,null,{default:n(()=>[o(" Accept "),t(B,{end:"",icon:"ri-checkbox-circle-line"})]),_:1}),t(e,{color:"secondary"},{default:n(()=>[t(B,{start:"",icon:"ri-subtract-line"}),o("Cancel ")]),_:1}),t(e,{color:"success"},{default:n(()=>[o(" Upload "),t(B,{end:"",icon:"ri-upload-cloud-line"})]),_:1}),t(e,{color:"info"},{default:n(()=>[t(B,{start:"",icon:"ri-arrow-left-line"}),o(" Back ")]),_:1}),t(e,{color:"warning"},{default:n(()=>[t(B,{icon:"ri-hammer-line"})]),_:1}),t(e,{color:"error"},{default:n(()=>[t(B,{icon:"ri-prohibited-line"})]),_:1})])}const Bt=d(ut,[["render",_t]]),Vt={},mt={class:"demo-space-x"};function ft(c,r){return s(),u("div",mt,[t(e,{variant:"tonal"},{default:n(()=>[o(" Primary ")]),_:1}),t(e,{color:"secondary",variant:"tonal"},{default:n(()=>[o(" Secondary ")]),_:1}),t(e,{color:"success",variant:"tonal"},{default:n(()=>[o(" Success ")]),_:1}),t(e,{color:"info",variant:"tonal"},{default:n(()=>[o(" Info ")]),_:1}),t(e,{color:"warning",variant:"tonal"},{default:n(()=>[o(" Warning ")]),_:1}),t(e,{color:"error",variant:"tonal"},{default:n(()=>[o(" Error ")]),_:1})])}const vt=d(Vt,[["render",ft]]),gt={},xt={class:"demo-space-x"};function ht(c,r){return s(),u("div",xt,[t(e,{variant:"plain"},{default:n(()=>[o(" Primary ")]),_:1}),t(e,{color:"secondary",variant:"plain"},{default:n(()=>[o(" Secondary ")]),_:1}),t(e,{color:"success",variant:"plain"},{default:n(()=>[o(" Success ")]),_:1}),t(e,{color:"info",variant:"plain"},{default:n(()=>[o(" Info ")]),_:1}),t(e,{color:"warning",variant:"plain"},{default:n(()=>[o(" Warning ")]),_:1}),t(e,{color:"error",variant:"plain"},{default:n(()=>[o(" Error ")]),_:1})])}const yt=d(gt,[["render",ht]]),bt={},wt={class:"demo-space-x"};function kt(c,r){return s(),u("div",wt,[t(e,{variant:"text"},{default:n(()=>[o(" Primary ")]),_:1}),t(e,{variant:"text",color:"secondary"},{default:n(()=>[o(" Secondary ")]),_:1}),t(e,{variant:"text",color:"success"},{default:n(()=>[o(" Success ")]),_:1}),t(e,{variant:"text",color:"info"},{default:n(()=>[o(" Info ")]),_:1}),t(e,{variant:"text",color:"warning"},{default:n(()=>[o(" Warning ")]),_:1}),t(e,{variant:"text",color:"error"},{default:n(()=>[o(" Error ")]),_:1})])}const St=d(bt,[["render",kt]]),$t={},It={class:"demo-space-x"};function Et(c,r){return s(),u("div",It,[t(e,null,{default:n(()=>[o(" Normal Button ")]),_:1}),t(e,{rounded:"",color:"secondary"},{default:n(()=>[o(" Rounded Button ")]),_:1}),t(e,{rounded:0,color:"success"},{default:n(()=>[o(" Tile Button ")]),_:1}),t(e,{rounded:"pill",color:"info"},{default:n(()=>[o(" Pill Button ")]),_:1})])}const Tt=d($t,[["render",Et]]),Pt={},Lt={class:"demo-space-x"};function jt(c,r){return s(),u("div",Lt,[t(e,{variant:"flat"},{default:n(()=>[o(" Primary ")]),_:1}),t(e,{variant:"flat",color:"secondary"},{default:n(()=>[o(" Secondary ")]),_:1}),t(e,{variant:"flat",color:"success"},{default:n(()=>[o(" Success ")]),_:1}),t(e,{variant:"flat",color:"info"},{default:n(()=>[o(" Info ")]),_:1}),t(e,{variant:"flat",color:"warning"},{default:n(()=>[o(" Warning ")]),_:1}),t(e,{variant:"flat",color:"error"},{default:n(()=>[o(" Error ")]),_:1})])}const Ct=d(Pt,[["render",jt]]),Wt={},Dt={class:"demo-space-x"};function zt(c,r){return s(),u("div",Dt,[t(e,{variant:"outlined"},{default:n(()=>[o(" Primary ")]),_:1}),t(e,{variant:"outlined",color:"secondary"},{default:n(()=>[o(" Secondary ")]),_:1}),t(e,{variant:"outlined",color:"success"},{default:n(()=>[o(" Success ")]),_:1}),t(e,{variant:"outlined",color:"info"},{default:n(()=>[o(" Info ")]),_:1}),t(e,{variant:"outlined",color:"warning"},{default:n(()=>[o(" Warning ")]),_:1}),t(e,{variant:"outlined",color:"error"},{default:n(()=>[o(" Error ")]),_:1})])}const Nt=d(Wt,[["render",zt]]),Rt={},Ut={class:"demo-space-x"};function Ot(c,r){return s(),u("div",Ut,[t(e,{color:"primary"},{default:n(()=>[o(" Primary ")]),_:1}),t(e,{color:"secondary"},{default:n(()=>[o(" Secondary ")]),_:1}),t(e,{color:"success"},{default:n(()=>[o(" Success ")]),_:1}),t(e,{color:"info"},{default:n(()=>[o(" Info ")]),_:1}),t(e,{color:"warning"},{default:n(()=>[o(" Warning ")]),_:1}),t(e,{color:"error"},{default:n(()=>[o(" Error ")]),_:1})])}const At=d(Rt,[["render",Ot]]),qt={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VBtn block>
        Block Button
      </VBtn>
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VBtn
        variant="outlined"
        block
      >
        Block Button
      </VBtn>
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VBtn block>
        Block Button
      </VBtn>
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VBtn
        variant="outlined"
        block
      >
        Block Button
      </VBtn>
    </VCol>
  </VRow>
</template>
`},Gt={ts:`<template>
  <div class="demo-space-x">
    <VBtn color="primary">
      Primary
    </VBtn>
    <VBtn color="secondary">
      Secondary
    </VBtn>
    <VBtn color="success">
      Success
    </VBtn>
    <VBtn color="info">
      Info
    </VBtn>
    <VBtn color="warning">
      Warning
    </VBtn>
    <VBtn color="error">
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn color="primary">
      Primary
    </VBtn>
    <VBtn color="secondary">
      Secondary
    </VBtn>
    <VBtn color="success">
      Success
    </VBtn>
    <VBtn color="info">
      Info
    </VBtn>
    <VBtn color="warning">
      Warning
    </VBtn>
    <VBtn color="error">
      Error
    </VBtn>
  </div>
</template>
`},Qt={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="flat">
      Primary
    </VBtn>

    <VBtn
      variant="flat"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="flat"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="flat"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="flat"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="flat"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="flat">
      Primary
    </VBtn>

    <VBtn
      variant="flat"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="flat"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="flat"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="flat"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="flat"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`},Yt={ts:`<script lang="ts" setup>
const toggleExclusive = ref(1)
<\/script>

<template>
  <VBtnToggle
    v-model="toggleExclusive"
    density="compact"
  >
    <VBtn icon="ri-align-left" />
    <VBtn icon="ri-align-center" />
    <VBtn icon="ri-align-right" />
    <VBtn icon="ri-align-justify" />
  </VBtnToggle>
</template>
`,js:`<script setup>
const toggleExclusive = ref(1)
<\/script>

<template>
  <VBtnToggle
    v-model="toggleExclusive"
    density="compact"
  >
    <VBtn icon="ri-align-left" />
    <VBtn icon="ri-align-center" />
    <VBtn icon="ri-align-right" />
    <VBtn icon="ri-align-justify" />
  </VBtnToggle>
</template>
`},Ft={ts:`<template>
  <div class="demo-space-x">
    <VBtn>
      Accept
      <VIcon
        end
        icon="ri-checkbox-circle-line"
      />
    </VBtn>

    <VBtn color="secondary">
      <VIcon
        start
        icon="ri-subtract-line"
      />Cancel
    </VBtn>

    <VBtn color="success">
      Upload
      <VIcon
        end
        icon="ri-upload-cloud-line"
      />
    </VBtn>

    <VBtn color="info">
      <VIcon
        start
        icon="ri-arrow-left-line"
      />
      Back
    </VBtn>

    <VBtn color="warning">
      <VIcon icon="ri-hammer-line" />
    </VBtn>

    <VBtn color="error">
      <VIcon icon="ri-prohibited-line" />
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn>
      Accept
      <VIcon
        end
        icon="ri-checkbox-circle-line"
      />
    </VBtn>

    <VBtn color="secondary">
      <VIcon
        start
        icon="ri-subtract-line"
      />Cancel
    </VBtn>

    <VBtn color="success">
      Upload
      <VIcon
        end
        icon="ri-upload-cloud-line"
      />
    </VBtn>

    <VBtn color="info">
      <VIcon
        start
        icon="ri-arrow-left-line"
      />
      Back
    </VBtn>

    <VBtn color="warning">
      <VIcon icon="ri-hammer-line" />
    </VBtn>

    <VBtn color="error">
      <VIcon icon="ri-prohibited-line" />
    </VBtn>
  </div>
</template>
`},Ht={ts:`<template>
  <div class="demo-space-x">
    <VBtn
      icon="ri-suitcase-line"
      variant="text"
    />

    <VBtn
      variant="text"
      icon="ri-user-add-line"
      color="secondary"
    />

    <VBtn
      icon="ri-search-line"
      variant="text"
      color="success"
    />

    <VBtn
      icon="ri-thumb-up-line"
      variant="text"
      color="info"
    />

    <VBtn
      icon="ri-star-line"
      variant="text"
      color="warning"
    />

    <VBtn
      icon="ri-heart-line"
      variant="text"
      color="error"
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn
      icon="ri-suitcase-line"
      variant="text"
    />

    <VBtn
      variant="text"
      icon="ri-user-add-line"
      color="secondary"
    />

    <VBtn
      icon="ri-search-line"
      variant="text"
      color="success"
    />

    <VBtn
      icon="ri-thumb-up-line"
      variant="text"
      color="info"
    />

    <VBtn
      icon="ri-star-line"
      variant="text"
      color="warning"
    />

    <VBtn
      icon="ri-heart-line"
      variant="text"
      color="error"
    />
  </div>
</template>
`},Jt={ts:`<template>
  <div class="demo-space-x">
    <VBtn href="https://pixinvent.com/">
      String Literal
    </VBtn>

    <VBtn
      href="https://pixinvent.com/"
      target="_blank"
      rel="noopener noreferrer"
    >
      Open New Tab
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn href="https://pixinvent.com/">
      String Literal
    </VBtn>

    <VBtn
      href="https://pixinvent.com/"
      target="_blank"
      rel="noopener noreferrer"
    >
      Open New Tab
    </VBtn>
  </div>
</template>
`},Kt={ts:`<script lang="ts" setup>
const loadings = ref<boolean[]>([])

const load = (i: number) => {
  loadings.value[i] = true
  setTimeout(() => {
    loadings.value[i] = false
  }, 3000)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn
      :loading="loadings[0]"
      :disabled="loadings[0]"
      color="primary"
      @click="load(0)"
    >
      Accept Terms
    </VBtn>

    <VBtn
      :loading="loadings[1]"
      :disabled="loadings[1]"
      color="secondary"
      @click="load(1)"
    >
      Upload
      <VIcon
        end
        icon="ri-upload-cloud-line"
      />
    </VBtn>

    <VBtn
      :loading="loadings[2]"
      :disabled="loadings[2]"
      color="success"
      @click="load(2)"
    >
      Loader slot
      <template #loader>
        <span>Loading...</span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[3]"
      :disabled="loadings[3]"
      color="info"
      @click="load(3)"
    >
      Icon Loader
      <template #loader>
        <span class="custom-loader">
          <VIcon icon="ri-refresh-line" />
        </span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[4]"
      :disabled="loadings[4]"
      color="warning"
      icon="ri-upload-cloud-line"
      @click="load(4)"
    />
  </div>
</template>

  <style lang="scss" scoped>
  .custom-loader {
    display: flex;
    animation: loader 1s infinite;
  }

  @keyframes loader {
    from {
      transform: rotate(0);
    }

    to {
      transform: rotate(360deg);
    }
  }
  </style>
`,js:`<script setup>
const loadings = ref([])

const load = i => {
  loadings.value[i] = true
  setTimeout(() => {
    loadings.value[i] = false
  }, 3000)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn
      :loading="loadings[0]"
      :disabled="loadings[0]"
      color="primary"
      @click="load(0)"
    >
      Accept Terms
    </VBtn>

    <VBtn
      :loading="loadings[1]"
      :disabled="loadings[1]"
      color="secondary"
      @click="load(1)"
    >
      Upload
      <VIcon
        end
        icon="ri-upload-cloud-line"
      />
    </VBtn>

    <VBtn
      :loading="loadings[2]"
      :disabled="loadings[2]"
      color="success"
      @click="load(2)"
    >
      Loader slot
      <template #loader>
        <span>Loading...</span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[3]"
      :disabled="loadings[3]"
      color="info"
      @click="load(3)"
    >
      Icon Loader
      <template #loader>
        <span class="custom-loader">
          <VIcon icon="ri-refresh-line" />
        </span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[4]"
      :disabled="loadings[4]"
      color="warning"
      icon="ri-upload-cloud-line"
      @click="load(4)"
    />
  </div>
</template>

  <style lang="scss" scoped>
  .custom-loader {
    display: flex;
    animation: loader 1s infinite;
  }

  @keyframes loader {
    from {
      transform: rotate(0);
    }

    to {
      transform: rotate(360deg);
    }
  }
  </style>
`},Mt={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="outlined">
      Primary
    </VBtn>
    <VBtn
      variant="outlined"
      color="secondary"
    >
      Secondary
    </VBtn>
    <VBtn
      variant="outlined"
      color="success"
    >
      Success
    </VBtn>
    <VBtn
      variant="outlined"
      color="info"
    >
      Info
    </VBtn>
    <VBtn
      variant="outlined"
      color="warning"
    >
      Warning
    </VBtn>
    <VBtn
      variant="outlined"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="outlined">
      Primary
    </VBtn>
    <VBtn
      variant="outlined"
      color="secondary"
    >
      Secondary
    </VBtn>
    <VBtn
      variant="outlined"
      color="success"
    >
      Success
    </VBtn>
    <VBtn
      variant="outlined"
      color="info"
    >
      Info
    </VBtn>
    <VBtn
      variant="outlined"
      color="warning"
    >
      Warning
    </VBtn>
    <VBtn
      variant="outlined"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`},Xt={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="plain">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="plain"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="plain"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="plain"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="plain"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="plain"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="plain">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="plain"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="plain"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="plain"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="plain"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="plain"
    >
      Error
    </VBtn>
  </div>
</template>
`},Zt={ts:`<template>
  <div class="demo-space-x">
    <VBtn>
      Normal Button
    </VBtn>
    <VBtn
      rounded
      color="secondary"
    >
      Rounded Button
    </VBtn>
    <VBtn
      :rounded="0"
      color="success"
    >
      Tile Button
    </VBtn>
    <VBtn
      rounded="pill"
      color="info"
    >
      Pill Button
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn>
      Normal Button
    </VBtn>
    <VBtn
      rounded
      color="secondary"
    >
      Rounded Button
    </VBtn>
    <VBtn
      :rounded="0"
      color="success"
    >
      Tile Button
    </VBtn>
    <VBtn
      rounded="pill"
      color="info"
    >
      Pill Button
    </VBtn>
  </div>
</template>
`},tn={ts:`<template>
  <div class="demo-space-x">
    <VBtn to="/components/alert">
      String Literal
    </VBtn>

    <VBtn
      color="warning"
      :to="{ path: '/components/alert' }"
    >
      Object Path
    </VBtn>

    <VBtn
      color="success"
      :to="{ name: 'components-alert' }"
    >
      Named Router
    </VBtn>

    <VBtn
      color="secondary"
      :to="{ path: '/components/alert', query: { plan: 'private' } }"
    >
      With Query
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn to="/components/alert">
      String Literal
    </VBtn>

    <VBtn
      color="warning"
      :to="{ path: '/components/alert' }"
    >
      Object Path
    </VBtn>

    <VBtn
      color="success"
      :to="{ name: 'components-alert' }"
    >
      Named Router
    </VBtn>

    <VBtn
      color="secondary"
      :to="{ path: '/components/alert', query: { plan: 'private' } }"
    >
      With Query
    </VBtn>
  </div>
</template>
`},nn={ts:`<template>
  <div class="demo-space-x">
    <VBtn size="x-large">
      Extra large Button
    </VBtn>

    <VBtn
      color="success"
      size="large"
    >
      Large Button
    </VBtn>

    <VBtn color="info">
      Normal Button
    </VBtn>

    <VBtn
      size="small"
      color="warning"
    >
      Small Button
    </VBtn>

    <VBtn
      size="x-small"
      color="error"
    >
      Extra small Button
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn size="x-large">
      Extra large Button
    </VBtn>

    <VBtn
      color="success"
      size="large"
    >
      Large Button
    </VBtn>

    <VBtn color="info">
      Normal Button
    </VBtn>

    <VBtn
      size="small"
      color="warning"
    >
      Small Button
    </VBtn>

    <VBtn
      size="x-small"
      color="error"
    >
      Extra small Button
    </VBtn>
  </div>
</template>
`},on={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="text">
      Primary
    </VBtn>

    <VBtn
      variant="text"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="text"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="text"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="text"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="text"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="text">
      Primary
    </VBtn>

    <VBtn
      variant="text"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="text"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="text"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="text"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="text"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`},en={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="tonal">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="tonal"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="tonal"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="tonal"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="tonal"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="tonal"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="tonal">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="tonal"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="tonal"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="tonal"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="tonal"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="tonal"
    >
      Error
    </VBtn>
  </div>
</template>
`},an=a("p",null,[o("The "),a("code",null,"color"),o(" prop is used to change the background color of the alert.")],-1),rn=a("p",null,[o("The "),a("code",null,"outlined"),o(" variant option is used to create outlined buttons.")],-1),ln=a("p",null,[o("The "),a("code",null,"flat"),o(" buttons still maintain their background color, but have no box shadow.")],-1),cn=a("p",null,[o("Use the "),a("code",null,"rounded"),o(" prop to control the border radius of buttons.")],-1),sn=a("p",null,[o("Use "),a("code",null,"text"),o(" variant option to create text button. Text buttons have no box shadow and no background.")],-1),dn=a("p",null,[o("Use "),a("code",null,"plain"),o(" variant option to a create a plain button. Plain buttons have a lower baseline opacity that reacts to hover and focus.")],-1),un=a("p",null,[o("Use "),a("code",null,"tonal"),o(" variant option to a create a light background button.")],-1),pn=a("p",null,"Icons can be used inside of buttons to add emphasis to the action.",-1),_n=a("p",null,[o("Use "),a("code",null,"icon"),o(" prop to create button with icon only. This property makes the button rounded and applies the text prop styles.")],-1),Bn=a("p",null,"Buttons can be given different sizing options to fit a multitude of scenarios.",-1),Vn=a("p",null,[o("The "),a("code",null,"block"),o(" prop allows buttons to extend the full available width.")],-1),mn=a("p",null,[o("Using the "),a("code",null,"loading"),o(" prop, you can notify a user that there is processing taking place. The default behavior is to use a "),a("code",null,"v-progress-circular"),o(" component but this can be customized.")],-1),fn=a("p",null,[o("Use "),a("code",null,"to"),o(" prop to create button with router support.")],-1),vn=a("p",null,[o("Designates that the component is a link. This is automatic when using the "),a("code",null,"href"),o(" or "),a("code",null,"to"),o(" prop.")],-1),gn=a("p",null,[o(" Wrap buttons with the "),a("code",null,"v-btn-toggle"),o(" component to create a group button. You can add a visual divider between buttons with the "),a("code",null,"divided"),o(" prop. ")],-1),Sn={__name:"button",setup(c){return(r,V)=>{const _=At,l=N,m=Nt,h=Ct,y=Tt,b=St,w=yt,k=vt,S=Bt,$=dt,I=lt,E=ot,T=Z,P=F,L=q,j=R;return s(),f(x,null,{default:n(()=>[t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Colors",code:Gt},{default:n(()=>[an,t(_)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Outlined",code:Mt},{default:n(()=>[rn,t(m)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Flat",code:Qt},{default:n(()=>[ln,t(h)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Rounded",code:Zt},{default:n(()=>[cn,t(y)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Text",code:on},{default:n(()=>[sn,t(b)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Plain",code:Xt},{default:n(()=>[dn,t(w)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Tonal",code:en},{default:n(()=>[un,t(k)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Icon",code:Ft},{default:n(()=>[pn,t(S)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Icon Only",code:Ht},{default:n(()=>[_n,t($)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Sizing",code:nn},{default:n(()=>[Bn,t(I)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Block",code:qt},{default:n(()=>[Vn,t(E)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Loaders",code:Kt},{default:n(()=>[mn,t(T)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Router",code:tn},{default:n(()=>[fn,t(v,{color:"warning",variant:"tonal",class:"mb-4"},{default:n(()=>[o(" Note: On click of the link button, You will get redirected to another page. ")]),_:1}),t(P)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Link",code:Jt},{default:n(()=>[vn,t(v,{color:"warning",variant:"tonal",class:"mb-4"},{default:n(()=>[o(" Note: On click of the link button, You will get redirected to another page. ")]),_:1}),t(L)]),_:1},8,["code"])]),_:1}),t(i,{cols:"12"},{default:n(()=>[t(l,{title:"Group",code:Yt},{default:n(()=>[gn,t(j)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{Sn as default};
