import{r as V,o as B,c as I,a,w as e,e as t,f as c,d as u,x as d,ez as m,m as v,F as T,g as w,j as C,h as g,b as k}from"./index-C8KGzm5p.js";import{_ as $}from"./AppCardCode-5Au5Cbm8.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-CA4PIBWr.js";import"./VCardText-DqmU0q3b.js";const O={class:"demo-space-x"},D={__name:"DemoSnackbarTransition",setup(_){const l=V(!1),p=V(!1),n=V(!1);return(i,b)=>(B(),I("div",O,[a(c,{onClick:b[0]||(b[0]=f=>l.value=!0)},{default:e(()=>[t(" fade snackbar ")]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":b[1]||(b[1]=f=>d(l)?l.value=f:null),transition:"fade-transition",location:"top start"},{default:e(()=>[t(" I'm a fade transition snackbar. ")]),_:1},8,["modelValue"]),a(c,{onClick:b[2]||(b[2]=f=>p.value=!0)},{default:e(()=>[t(" Scale snackbar ")]),_:1}),a(m,{modelValue:u(p),"onUpdate:modelValue":b[3]||(b[3]=f=>d(p)?p.value=f:null),transition:"scale-transition",location:"bottom end"},{default:e(()=>[t(" I'm a scale transition snackbar. ")]),_:1},8,["modelValue"]),a(c,{onClick:b[4]||(b[4]=f=>n.value=!0)},{default:e(()=>[t(" scroll y reverse ")]),_:1}),a(m,{modelValue:u(n),"onUpdate:modelValue":b[5]||(b[5]=f=>d(n)?n.value=f:null),transition:"scroll-y-reverse-transition",location:"top end"},{default:e(()=>[t(" I'm a scroll y reverse transition snackbar. ")]),_:1},8,["modelValue"])]))}},U={class:"demo-space-x"},E={__name:"DemoSnackbarVariants",setup(_){const l=V(!1),p=V(!1),n=V(!1),i=V(!1),b=V(!1);return(f,s)=>(B(),I("div",U,[a(c,{onClick:s[0]||(s[0]=S=>l.value=!0)},{default:e(()=>[t(" Default ")]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":s[1]||(s[1]=S=>d(l)?l.value=S:null),location:"top start"},{default:e(()=>[t(" Jelly chocolate bar candy canes apple pie. ")]),_:1},8,["modelValue"]),a(c,{onClick:s[2]||(s[2]=S=>p.value=!0)},{default:e(()=>[t(" tonal ")]),_:1}),a(m,{modelValue:u(p),"onUpdate:modelValue":s[3]||(s[3]=S=>d(p)?p.value=S:null),location:"top end",variant:"tonal"},{default:e(()=>[t(" Ice cream cake candy canes. ")]),_:1},8,["modelValue"]),a(c,{onClick:s[4]||(s[4]=S=>n.value=!0)},{default:e(()=>[t(" Text ")]),_:1}),a(m,{modelValue:u(n),"onUpdate:modelValue":s[5]||(s[5]=S=>d(n)?n.value=S:null),location:"end center",variant:"text"},{default:e(()=>[t(" Pie icing biscuit soufflé liquorice topping. ")]),_:1},8,["modelValue"]),a(c,{onClick:s[6]||(s[6]=S=>i.value=!0)},{default:e(()=>[t(" Outlined ")]),_:1}),a(m,{modelValue:u(i),"onUpdate:modelValue":s[7]||(s[7]=S=>d(i)?i.value=S:null),location:"bottom end",variant:"outlined",color:"error"},{default:e(()=>[t(" Oat cake caramels sesame snaps candy. ")]),_:1},8,["modelValue"]),a(c,{onClick:s[8]||(s[8]=S=>b.value=!0)},{default:e(()=>[t(" Flat ")]),_:1}),a(m,{modelValue:u(b),"onUpdate:modelValue":s[9]||(s[9]=S=>d(b)?b.value=S:null),location:"bottom start",variant:"flat",color:"error"},{default:e(()=>[t(" Oat cake caramels sesame snaps candy. ")]),_:1},8,["modelValue"])]))}},F={class:"demo-space-x"},j={__name:"DemoSnackbarPosition",setup(_){const l=V(!1),p=V(!1),n=V(!1),i=V(!1),b=V(!1),f=V(!1),s=V(!1),S=V(!1),x=V(!1);return(y,o)=>(B(),I("div",F,[a(c,{icon:"",variant:"text",onClick:o[0]||(o[0]=r=>p.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-up-line"})]),_:1}),a(m,{modelValue:u(p),"onUpdate:modelValue":o[1]||(o[1]=r=>d(p)?p.value=r:null),location:"top"},{default:e(()=>[t(" I'm a top snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[2]||(o[2]=r=>n.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-right-up-line"})]),_:1}),a(m,{modelValue:u(n),"onUpdate:modelValue":o[3]||(o[3]=r=>d(n)?n.value=r:null),location:"top end"},{default:e(()=>[t(" I'm a top right snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[4]||(o[4]=r=>s.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-right-line"})]),_:1}),a(m,{modelValue:u(s),"onUpdate:modelValue":o[5]||(o[5]=r=>d(s)?s.value=r:null),location:"end center"},{default:e(()=>[t(" I'm a center end snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[6]||(o[6]=r=>i.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-right-down-line"})]),_:1}),a(m,{modelValue:u(i),"onUpdate:modelValue":o[7]||(o[7]=r=>d(i)?i.value=r:null),location:"bottom end"},{default:e(()=>[t(" I'm a bottom end snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[8]||(o[8]=r=>b.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-down-line"})]),_:1}),a(m,{modelValue:u(b),"onUpdate:modelValue":o[9]||(o[9]=r=>d(b)?b.value=r:null)},{default:e(()=>[t(" I'm a bottom snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[10]||(o[10]=r=>f.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-left-down-line"})]),_:1}),a(m,{modelValue:u(f),"onUpdate:modelValue":o[11]||(o[11]=r=>d(f)?f.value=r:null),location:"bottom start"},{default:e(()=>[t(" I'm a bottom start snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[12]||(o[12]=r=>S.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-left-line"})]),_:1}),a(m,{modelValue:u(S),"onUpdate:modelValue":o[13]||(o[13]=r=>d(S)?S.value=r:null),location:"start center"},{default:e(()=>[t(" I'm a center start snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[14]||(o[14]=r=>l.value=!0)},{default:e(()=>[a(v,{icon:"ri-arrow-left-up-line"})]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":o[15]||(o[15]=r=>d(l)?l.value=r:null),location:"top start"},{default:e(()=>[t(" I'm a top start snackbar. ")]),_:1},8,["modelValue"]),a(c,{icon:"",variant:"text",onClick:o[16]||(o[16]=r=>x.value=!0)},{default:e(()=>[a(v,{icon:"ri-drag-move-fill"})]),_:1}),a(m,{modelValue:u(x),"onUpdate:modelValue":o[17]||(o[17]=r=>d(x)?x.value=r:null),location:"center"},{default:e(()=>[t(" I'm a center snackbar. ")]),_:1},8,["modelValue"])]))}},R={__name:"DemoSnackbarVertical",setup(_){const l=V(!1);return(p,n)=>(B(),I(T,null,[a(c,{onClick:n[0]||(n[0]=i=>l.value=!0)},{default:e(()=>[t(" Open Snackbar ")]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":n[3]||(n[3]=i=>d(l)?l.value=i:null),vertical:""},{actions:e(()=>[a(c,{color:"success",onClick:n[1]||(n[1]=i=>l.value=!1)},{default:e(()=>[t(" Undo ")]),_:1}),a(c,{color:"error",onClick:n[2]||(n[2]=i=>l.value=!1)},{default:e(()=>[t(" Close ")]),_:1})]),default:e(()=>[t(" Sugar plum chocolate bar halvah sesame snaps apple pie donut croissant marshmallow. Sweet roll donut gummies sesame snaps icing bear claw tiramisu cotton candy. ")]),_:1},8,["modelValue"])],64))}},q={__name:"DemoSnackbarTimeout",setup(_){const l=V(!1);return(p,n)=>(B(),I(T,null,[a(c,{onClick:n[0]||(n[0]=i=>l.value=!0)},{default:e(()=>[t(" Open Snackbar ")]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":n[1]||(n[1]=i=>d(l)?l.value=i:null),timeout:2e3},{default:e(()=>[t(" My timeout is set to 2000. ")]),_:1},8,["modelValue"])],64))}},A={__name:"DemoSnackbarMultiLine",setup(_){const l=V(!1);return(p,n)=>(B(),I(T,null,[a(c,{onClick:n[0]||(n[0]=i=>l.value=!0)},{default:e(()=>[t(" Open Snackbar ")]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":n[2]||(n[2]=i=>d(l)?l.value=i:null),"multi-line":""},{actions:e(()=>[a(c,{color:"error",onClick:n[1]||(n[1]=i=>l.value=!1)},{default:e(()=>[t(" Close ")]),_:1})]),default:e(()=>[t(" I am a multi-line snackbar. I can have more than one line. This is another line that is quite long. ")]),_:1},8,["modelValue"])],64))}},H={__name:"DemoSnackbarWithAction",setup(_){const l=V(!1);return(p,n)=>(B(),I(T,null,[a(c,{onClick:n[0]||(n[0]=i=>l.value=!0)},{default:e(()=>[t(" Open Snackbar ")]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":n[2]||(n[2]=i=>d(l)?l.value=i:null)},{actions:e(()=>[a(c,{color:"error",onClick:n[1]||(n[1]=i=>l.value=!1)},{default:e(()=>[t(" Close ")]),_:1})]),default:e(()=>[t(" Hello, I'm a snackbar with actions. ")]),_:1},8,["modelValue"])],64))}},M={__name:"DemoSnackbarBasic",setup(_){const l=V(!1);return(p,n)=>(B(),I(T,null,[a(c,{onClick:n[0]||(n[0]=i=>l.value=!0)},{default:e(()=>[t(" Open Snackbar ")]),_:1}),a(m,{modelValue:u(l),"onUpdate:modelValue":n[1]||(n[1]=i=>d(l)?l.value=i:null)},{default:e(()=>[t(" Hello, I'm a snackbar ")]),_:1},8,["modelValue"])],64))}},P={ts:`<script lang="ts" setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <!-- SnackBar -->
  <VSnackbar v-model="isSnackbarVisible">
    Hello, I'm a snackbar
  </VSnackbar>
</template>
`,js:`<script setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <!-- SnackBar -->
  <VSnackbar v-model="isSnackbarVisible">
    Hello, I'm a snackbar
  </VSnackbar>
</template>
`},L={ts:`<script lang="ts" setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <!-- Snackbar -->
  <VSnackbar
    v-model="isSnackbarVisible"
    multi-line
  >
    I am a multi-line snackbar. I can have more than one line. This is another line that is quite long.

    <template #actions>
      <VBtn
        color="error"
        @click="isSnackbarVisible = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>
`,js:`<script setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <!-- Snackbar -->
  <VSnackbar
    v-model="isSnackbarVisible"
    multi-line
  >
    I am a multi-line snackbar. I can have more than one line. This is another line that is quite long.

    <template #actions>
      <VBtn
        color="error"
        @click="isSnackbarVisible = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>
`},J={ts:`<script lang="ts" setup>
const isSnackbarTopStartVisible = ref(false)
const isSnackbarTopVisible = ref(false)
const isSnackbarTopEndVisible = ref(false)
const isSnackbarBottomEndVisible = ref(false)
const isSnackbarBottomVisible = ref(false)
const isSnackbarBottomStartVisible = ref(false)
const isSnackbarEndVisible = ref(false)
const isSnackbarStartVisible = ref(false)
const isSnackbarCenteredVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <!-- top  -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarTopVisible = true"
    >
      <VIcon icon="ri-arrow-up-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarTopVisible"
      location="top"
    >
      I'm a top snackbar.
    </VSnackbar>

    <!-- top end -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarTopEndVisible = true"
    >
      <VIcon icon="ri-arrow-right-up-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      location="top end"
    >
      I'm a top right snackbar.
    </VSnackbar>

    <!-- center end -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarEndVisible = true"
    >
      <VIcon icon="ri-arrow-right-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarEndVisible"
      location="end center"
    >
      I'm a center end snackbar.
    </VSnackbar>

    <!-- bottom end -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarBottomEndVisible = true"
    >
      <VIcon icon="ri-arrow-right-down-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarBottomEndVisible"
      location="bottom end"
    >
      I'm a bottom end snackbar.
    </VSnackbar>

    <!-- bottom -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarBottomVisible = true"
    >
      <VIcon icon="ri-arrow-down-line" />
    </VBtn>

    <VSnackbar v-model="isSnackbarBottomVisible">
      I'm a bottom snackbar.
    </VSnackbar>

    <!-- bottom start -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarBottomStartVisible = true"
    >
      <VIcon icon="ri-arrow-left-down-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarBottomStartVisible"
      location="bottom start"
    >
      I'm a bottom start snackbar.
    </VSnackbar>

    <!-- center start -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarStartVisible = true"
    >
      <VIcon icon="ri-arrow-left-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarStartVisible"
      location="start center"
    >
      I'm a center start snackbar.
    </VSnackbar>

    <!-- top start -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarTopStartVisible = true"
    >
      <VIcon icon="ri-arrow-left-up-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarTopStartVisible"
      location="top start"
    >
      I'm a top start snackbar.
    </VSnackbar>

    <!-- center -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarCenteredVisible = true"
    >
      <VIcon icon="ri-drag-move-fill" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarCenteredVisible"
      location="center"
    >
      I'm a center snackbar.
    </VSnackbar>
  </div>
</template>
`,js:`<script setup>
const isSnackbarTopStartVisible = ref(false)
const isSnackbarTopVisible = ref(false)
const isSnackbarTopEndVisible = ref(false)
const isSnackbarBottomEndVisible = ref(false)
const isSnackbarBottomVisible = ref(false)
const isSnackbarBottomStartVisible = ref(false)
const isSnackbarEndVisible = ref(false)
const isSnackbarStartVisible = ref(false)
const isSnackbarCenteredVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <!-- top  -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarTopVisible = true"
    >
      <VIcon icon="ri-arrow-up-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarTopVisible"
      location="top"
    >
      I'm a top snackbar.
    </VSnackbar>

    <!-- top end -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarTopEndVisible = true"
    >
      <VIcon icon="ri-arrow-right-up-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarTopEndVisible"
      location="top end"
    >
      I'm a top right snackbar.
    </VSnackbar>

    <!-- center end -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarEndVisible = true"
    >
      <VIcon icon="ri-arrow-right-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarEndVisible"
      location="end center"
    >
      I'm a center end snackbar.
    </VSnackbar>

    <!-- bottom end -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarBottomEndVisible = true"
    >
      <VIcon icon="ri-arrow-right-down-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarBottomEndVisible"
      location="bottom end"
    >
      I'm a bottom end snackbar.
    </VSnackbar>

    <!-- bottom -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarBottomVisible = true"
    >
      <VIcon icon="ri-arrow-down-line" />
    </VBtn>

    <VSnackbar v-model="isSnackbarBottomVisible">
      I'm a bottom snackbar.
    </VSnackbar>

    <!-- bottom start -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarBottomStartVisible = true"
    >
      <VIcon icon="ri-arrow-left-down-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarBottomStartVisible"
      location="bottom start"
    >
      I'm a bottom start snackbar.
    </VSnackbar>

    <!-- center start -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarStartVisible = true"
    >
      <VIcon icon="ri-arrow-left-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarStartVisible"
      location="start center"
    >
      I'm a center start snackbar.
    </VSnackbar>

    <!-- top start -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarTopStartVisible = true"
    >
      <VIcon icon="ri-arrow-left-up-line" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarTopStartVisible"
      location="top start"
    >
      I'm a top start snackbar.
    </VSnackbar>

    <!-- center -->
    <VBtn
      icon
      variant="text"
      @click="isSnackbarCenteredVisible = true"
    >
      <VIcon icon="ri-drag-move-fill" />
    </VBtn>

    <VSnackbar
      v-model="isSnackbarCenteredVisible"
      location="center"
    >
      I'm a center snackbar.
    </VSnackbar>
  </div>
</template>
`},N={ts:`<script lang="ts" setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <!-- Snackbar -->
  <VSnackbar
    v-model="isSnackbarVisible"
    :timeout="2000"
  >
    My timeout is set to 2000.
  </VSnackbar>
</template>
`,js:`<script setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <!-- Snackbar -->
  <VSnackbar
    v-model="isSnackbarVisible"
    :timeout="2000"
  >
    My timeout is set to 2000.
  </VSnackbar>
</template>
`},W={ts:`<script lang="ts" setup>
const isSnackbarFadeVisible = ref(false)
const isSnackbarScaleVisible = ref(false)
const isSnackbarScrollReverseVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <!-- fade -->
    <VBtn @click="isSnackbarFadeVisible = true">
      fade snackbar
    </VBtn>

    <VSnackbar
      v-model="isSnackbarFadeVisible"
      transition="fade-transition"
      location="top start"
    >
      I'm a fade transition snackbar.
    </VSnackbar>

    <!-- scale -->
    <VBtn @click="isSnackbarScaleVisible = true">
      Scale snackbar
    </VBtn>

    <VSnackbar
      v-model="isSnackbarScaleVisible"
      transition="scale-transition"
      location="bottom end"
    >
      I'm a scale transition snackbar.
    </VSnackbar>

    <!-- scroll y reverse -->
    <VBtn @click="isSnackbarScrollReverseVisible = true">
      scroll y reverse
    </VBtn>

    <VSnackbar
      v-model="isSnackbarScrollReverseVisible"
      transition="scroll-y-reverse-transition"
      location="top end"
    >
      I'm a scroll y reverse transition snackbar.
    </VSnackbar>
  </div>
</template>
`,js:`<script setup>
const isSnackbarFadeVisible = ref(false)
const isSnackbarScaleVisible = ref(false)
const isSnackbarScrollReverseVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <!-- fade -->
    <VBtn @click="isSnackbarFadeVisible = true">
      fade snackbar
    </VBtn>

    <VSnackbar
      v-model="isSnackbarFadeVisible"
      transition="fade-transition"
      location="top start"
    >
      I'm a fade transition snackbar.
    </VSnackbar>

    <!-- scale -->
    <VBtn @click="isSnackbarScaleVisible = true">
      Scale snackbar
    </VBtn>

    <VSnackbar
      v-model="isSnackbarScaleVisible"
      transition="scale-transition"
      location="bottom end"
    >
      I'm a scale transition snackbar.
    </VSnackbar>

    <!-- scroll y reverse -->
    <VBtn @click="isSnackbarScrollReverseVisible = true">
      scroll y reverse
    </VBtn>

    <VSnackbar
      v-model="isSnackbarScrollReverseVisible"
      transition="scroll-y-reverse-transition"
      location="top end"
    >
      I'm a scroll y reverse transition snackbar.
    </VSnackbar>
  </div>
</template>
`},z={ts:`<script lang="ts" setup>
const isDefaultSnackbarVisible = ref(false)
const isTonalSnackbarVisible = ref(false)
const isTextSnackbarVisible = ref(false)
const isOutlinedSnackbarVisible = ref(false)
const isFlatSnackbarVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <!-- Default toggle btn -->
    <VBtn @click="isDefaultSnackbarVisible = true">
      Default
    </VBtn>

    <!-- Default snackbar -->
    <VSnackbar
      v-model="isDefaultSnackbarVisible"
      location="top start"
    >
      Jelly chocolate bar candy canes apple pie.
    </VSnackbar>

    <!-- tonal toggle btn -->
    <VBtn @click="isTonalSnackbarVisible = true">
      tonal
    </VBtn>

    <!-- tonal snackbar -->
    <VSnackbar
      v-model="isTonalSnackbarVisible"
      location="top end"
      variant="tonal"
    >
      Ice cream cake candy canes.
    </VSnackbar>

    <!-- Text toggle btn -->
    <VBtn @click="isTextSnackbarVisible = true">
      Text
    </VBtn>

    <!-- Text snackbar -->
    <VSnackbar
      v-model="isTextSnackbarVisible"
      location="end center"
      variant="text"
    >
      Pie icing biscuit soufflé liquorice topping.
    </VSnackbar>

    <!-- Outline toggle btn -->
    <VBtn @click="isOutlinedSnackbarVisible = true">
      Outlined
    </VBtn>

    <!-- Outline snackbar -->
    <VSnackbar
      v-model="isOutlinedSnackbarVisible"
      location="bottom end"
      variant="outlined"
      color="error"
    >
      Oat cake caramels sesame snaps candy.
    </VSnackbar>

    <!-- flat toggle btn -->
    <VBtn @click="isFlatSnackbarVisible = true">
      Flat
    </VBtn>

    <!-- flat snackbar -->
    <VSnackbar
      v-model="isFlatSnackbarVisible"
      location="bottom start"
      variant="flat"
      color="error"
    >
      Oat cake caramels sesame snaps candy.
    </VSnackbar>
  </div>
</template>
`,js:`<script setup>
const isDefaultSnackbarVisible = ref(false)
const isTonalSnackbarVisible = ref(false)
const isTextSnackbarVisible = ref(false)
const isOutlinedSnackbarVisible = ref(false)
const isFlatSnackbarVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <!-- Default toggle btn -->
    <VBtn @click="isDefaultSnackbarVisible = true">
      Default
    </VBtn>

    <!-- Default snackbar -->
    <VSnackbar
      v-model="isDefaultSnackbarVisible"
      location="top start"
    >
      Jelly chocolate bar candy canes apple pie.
    </VSnackbar>

    <!-- tonal toggle btn -->
    <VBtn @click="isTonalSnackbarVisible = true">
      tonal
    </VBtn>

    <!-- tonal snackbar -->
    <VSnackbar
      v-model="isTonalSnackbarVisible"
      location="top end"
      variant="tonal"
    >
      Ice cream cake candy canes.
    </VSnackbar>

    <!-- Text toggle btn -->
    <VBtn @click="isTextSnackbarVisible = true">
      Text
    </VBtn>

    <!-- Text snackbar -->
    <VSnackbar
      v-model="isTextSnackbarVisible"
      location="end center"
      variant="text"
    >
      Pie icing biscuit soufflé liquorice topping.
    </VSnackbar>

    <!-- Outline toggle btn -->
    <VBtn @click="isOutlinedSnackbarVisible = true">
      Outlined
    </VBtn>

    <!-- Outline snackbar -->
    <VSnackbar
      v-model="isOutlinedSnackbarVisible"
      location="bottom end"
      variant="outlined"
      color="error"
    >
      Oat cake caramels sesame snaps candy.
    </VSnackbar>

    <!-- flat toggle btn -->
    <VBtn @click="isFlatSnackbarVisible = true">
      Flat
    </VBtn>

    <!-- flat snackbar -->
    <VSnackbar
      v-model="isFlatSnackbarVisible"
      location="bottom start"
      variant="flat"
      color="error"
    >
      Oat cake caramels sesame snaps candy.
    </VSnackbar>
  </div>
</template>
`},G={ts:`<script lang="ts" setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <VSnackbar
    v-model="isSnackbarVisible"
    vertical
  >
    Sugar plum chocolate bar halvah sesame snaps apple pie donut croissant marshmallow. Sweet roll donut gummies sesame snaps icing bear claw tiramisu cotton candy.

    <template #actions>
      <VBtn
        color="success"
        @click="isSnackbarVisible = false"
      >
        Undo
      </VBtn>

      <VBtn
        color="error"
        @click="isSnackbarVisible = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>
`,js:`<script setup>
const isSnackbarVisible = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisible = true">
    Open Snackbar
  </VBtn>

  <VSnackbar
    v-model="isSnackbarVisible"
    vertical
  >
    Sugar plum chocolate bar halvah sesame snaps apple pie donut croissant marshmallow. Sweet roll donut gummies sesame snaps icing bear claw tiramisu cotton candy.

    <template #actions>
      <VBtn
        color="success"
        @click="isSnackbarVisible = false"
      >
        Undo
      </VBtn>

      <VBtn
        color="error"
        @click="isSnackbarVisible = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>
`},K={ts:`<script lang="ts" setup>
const isSnackbarVisibility = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisibility = true">
    Open Snackbar
  </VBtn>

  <!-- Snackbar -->
  <VSnackbar v-model="isSnackbarVisibility">
    Hello, I'm a snackbar with actions.

    <template #actions>
      <VBtn
        color="error"
        @click="isSnackbarVisibility = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>
`,js:`<script setup>
const isSnackbarVisibility = ref(false)
<\/script>

<template>
  <VBtn @click="isSnackbarVisibility = true">
    Open Snackbar
  </VBtn>

  <!-- Snackbar -->
  <VSnackbar v-model="isSnackbarVisibility">
    Hello, I'm a snackbar with actions.

    <template #actions>
      <VBtn
        color="error"
        @click="isSnackbarVisibility = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>
`},Q=k("p",null,[t("The "),k("code",null,"v-snackbar"),t(" component is used to display a quick message to a user. Snackbars support positioning, removal delay, and callbacks.")],-1),X=k("p",null,[t("Use "),k("code",null,"actions"),t(" slot to add action button. A "),k("code",null,"v-snackbar"),t(" in its simplest form displays a temporary and closable notification to the user.")],-1),Y=k("p",null,[t("The "),k("code",null,"multi-line"),t(" property extends the height of the "),k("code",null,"v-snackbar"),t(" to give you a little more room for content.")],-1),Z=k("p",null,[t("The "),k("code",null,"timeout"),t(" property lets you customize the delay before the "),k("code",null,"v-snackbar"),t(" is hidden.")],-1),h=k("p",null,[t("The "),k("code",null,"vertical"),t(" property allows you to stack the content of your "),k("code",null,"v-snackbar"),t(".")],-1),aa=k("p",null,[t("Use "),k("code",null,"location"),t(" prop to change the position of snackbar.")],-1),ea=k("p",null,[t("Apply different styles to the snackbar using props such as "),k("code",null,"shaped"),t(", "),k("code",null,"rounded"),t(", "),k("code",null,"color"),t(", "),k("code",null,"text"),t(", "),k("code",null,"outlined"),t(", "),k("code",null,"tile"),t(" and more.")],-1),ta=k("p",null,"Use transition prop to sets the component transition.",-1),ra={__name:"snackbar",setup(_){return(l,p)=>{const n=M,i=$,b=H,f=A,s=q,S=R,x=j,y=E,o=D;return B(),w(C,{class:"match-height"},{default:e(()=>[a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"Basic",code:P},{default:e(()=>[Q,a(n)]),_:1},8,["code"])]),_:1}),a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"With Action",code:K},{default:e(()=>[X,a(b)]),_:1},8,["code"])]),_:1}),a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"Multi Line",code:L},{default:e(()=>[Y,a(f)]),_:1},8,["code"])]),_:1}),a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"Timeout",code:N},{default:e(()=>[Z,a(s)]),_:1},8,["code"])]),_:1}),a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"Vertical",code:G},{default:e(()=>[h,a(S)]),_:1},8,["code"])]),_:1}),a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"Position",code:J},{default:e(()=>[aa,a(x)]),_:1},8,["code"])]),_:1}),a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"Variants",code:z},{default:e(()=>[ea,a(y)]),_:1},8,["code"])]),_:1}),a(g,{cols:"12",md:"6"},{default:e(()=>[a(i,{title:"Transition",code:W},{default:e(()=>[ta,a(o)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{ra as default};
