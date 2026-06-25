import{o as s,c as _,a as o,w as t,e as n,f as e,V as y,d as V,H as $,v as S,m,am as T,b as a,_ as v,r as H,g as f,j as b,h as d}from"./index-C8KGzm5p.js";import{V as i}from"./VTooltip--tJE-x4I.js";import{_ as D}from"./AppCardCode-5Au5Cbm8.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-CA4PIBWr.js";import"./VCardText-DqmU0q3b.js";const k={class:"demo-space-x"},E=a("span",null,"Tooltip on Icon",-1),I={__name:"DemoTooltipTooltipOnVariousElements",setup(r){return(l,u)=>(s(),_("div",k,[o(e,null,{default:t(()=>[n(" Button "),o(i,{location:"top",activator:"parent"},{default:t(()=>[n(" Tooltip ")]),_:1})]),_:1}),o(S,{color:"info"},{default:t(()=>[o(y,{src:V($)},null,8,["src"]),o(i,{location:"top",activator:"parent"},{default:t(()=>[n(" Tooltip on Avatar ")]),_:1})]),_:1}),o(i,{location:"top"},{activator:t(({props:c})=>[o(m,T(c,{size:"30",icon:"ri-user-line"}),null,16)]),default:t(()=>[E]),_:1})]))}},A={},w={class:"demo-space-x"},j=a("span",null,"Scale Transition",-1),F=a("span",null,"Scroll X Transition",-1),X=a("span",null,"Scroll Y Transition",-1);function C(r,l){return s(),_("div",w,[o(e,null,{default:t(()=>[n(" scale transition "),o(i,{location:"top",transition:"scale-transition",activator:"parent"},{default:t(()=>[j]),_:1})]),_:1}),o(e,null,{default:t(()=>[n(" scroll X transition "),o(i,{location:"top",activator:"parent",transition:"scroll-x-transition"},{default:t(()=>[F]),_:1})]),_:1}),o(e,null,{default:t(()=>[n(" scroll y transition "),o(i,{location:"top",activator:"parent",transition:"scroll-y-transition"},{default:t(()=>[X]),_:1})]),_:1})])}const M=v(A,[["render",C]]),P={class:"demo-space-x"},z=a("span",null,"Programmatic tooltip",-1),N={__name:"DemoTooltipVModelSupport",setup(r){const l=H(!1);return(u,c)=>(s(),_("div",P,[o(e,{onClick:c[0]||(c[0]=p=>l.value=!V(l))},{default:t(()=>[n(" toggle tooltip ")]),_:1}),o(i,{"model-value":V(l),location:"top"},{activator:t(({props:p})=>[o(m,T(p,{icon:"ri-instagram-line"}),null,16)]),default:t(()=>[z]),_:1},8,["model-value"])]))}},Y={},L=a("span",null,"Open Delay On Hover",-1),U=a("span",null,"Open Delay On Hover",-1);function R(r,l){return s(),f(e,{variant:"outlined"},{default:t(()=>[L,n(),o(i,{"open-delay":"500",location:"top",activator:"parent"},{default:t(()=>[U]),_:1})]),_:1})}const q=v(Y,[["render",R]]),G={},J={class:"demo-space-x"},K=a("span",null,"Open On Hover",-1),Q=a("span",null,"Open On click",-1),W=a("span",null,"Open On Hover + Focus",-1);function Z(r,l){return s(),_("div",J,[o(e,{variant:"outlined"},{default:t(()=>[K,o(i,{activator:"parent",location:"top"},{default:t(()=>[n(" Open On Hover ")]),_:1})]),_:1}),o(e,{variant:"outlined",color:"primary"},{default:t(()=>[Q,o(i,{"open-on-click":"","open-on-hover":!1,location:"top",activator:"parent"},{default:t(()=>[n(" Open On click ")]),_:1})]),_:1}),o(e,{variant:"outlined"},{default:t(()=>[W,o(i,{"open-on-focus":"",location:"top",activator:"parent"},{default:t(()=>[n(" Open On Hover + Focus ")]),_:1})]),_:1})])}const oo=v(G,[["render",Z]]),to={},no={class:"demo-space-x"};function ao(r,l){return s(),_("div",no,[o(e,{variant:"tonal"},{default:t(()=>[n(" Tooltip on End "),o(i,{activator:"parent",location:"end"},{default:t(()=>[n(" End Tooltip ")]),_:1})]),_:1}),o(e,{variant:"tonal"},{default:t(()=>[n(" Tooltip on Start "),o(i,{activator:"parent",location:"start"},{default:t(()=>[n(" Start Tooltip ")]),_:1})]),_:1}),o(e,{variant:"tonal"},{default:t(()=>[n(" Tooltip on Top "),o(i,{activator:"parent",location:"top"},{default:t(()=>[n(" Top Tooltip ")]),_:1})]),_:1}),o(e,{variant:"tonal"},{default:t(()=>[n(" Tooltip on Bottom "),o(i,{activator:"parent",location:"bottom"},{default:t(()=>[n(" Bottom Tooltip ")]),_:1})]),_:1})])}const io=v(to,[["render",ao]]),eo={ts:`<template>
  <!-- Delay on open -->
  <VBtn variant="outlined">
    <span>Open Delay On Hover</span> <VTooltip
      open-delay="500"
      location="top"
      activator="parent"
    >
      <span>Open Delay On Hover</span>
    </VTooltip>
  </VBtn>
</template>
`,js:`<template>
  <!-- Delay on open -->
  <VBtn variant="outlined">
    <span>Open Delay On Hover</span> <VTooltip
      open-delay="500"
      location="top"
      activator="parent"
    >
      <span>Open Delay On Hover</span>
    </VTooltip>
  </VBtn>
</template>
`},lo={ts:`<template>
  <div class="demo-space-x">
    <!-- open on hover -->
    <VBtn variant="outlined">
      <span>Open On Hover</span>
      <VTooltip
        activator="parent"
        location="top"
      >
        Open On Hover
      </VTooltip>
    </VBtn>

    <!-- open on click -->
    <VBtn
      variant="outlined"
      color="primary"
    >
      <span>Open On click</span>

      <VTooltip
        open-on-click
        :open-on-hover="false"
        location="top"
        activator="parent"
      >
        Open On click
      </VTooltip>
    </VBtn>

    <!-- open on focus -->
    <VBtn variant="outlined">
      <span>Open On Hover + Focus</span>
      <VTooltip
        open-on-focus
        location="top"
        activator="parent"
      >
        Open On Hover + Focus
      </VTooltip>
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <!-- open on hover -->
    <VBtn variant="outlined">
      <span>Open On Hover</span>
      <VTooltip
        activator="parent"
        location="top"
      >
        Open On Hover
      </VTooltip>
    </VBtn>

    <!-- open on click -->
    <VBtn
      variant="outlined"
      color="primary"
    >
      <span>Open On click</span>

      <VTooltip
        open-on-click
        :open-on-hover="false"
        location="top"
        activator="parent"
      >
        Open On click
      </VTooltip>
    </VBtn>

    <!-- open on focus -->
    <VBtn variant="outlined">
      <span>Open On Hover + Focus</span>
      <VTooltip
        open-on-focus
        location="top"
        activator="parent"
      >
        Open On Hover + Focus
      </VTooltip>
    </VBtn>
  </div>
</template>
`},po={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="tonal">
      Tooltip on End
      <VTooltip
        activator="parent"
        location="end"
      >
        End Tooltip
      </VTooltip>
    </VBtn>

    <VBtn variant="tonal">
      Tooltip on Start
      <VTooltip
        activator="parent"
        location="start"
      >
        Start Tooltip
      </VTooltip>
    </VBtn>

    <VBtn variant="tonal">
      Tooltip on Top
      <VTooltip
        activator="parent"
        location="top"
      >
        Top Tooltip
      </VTooltip>
    </VBtn>

    <VBtn variant="tonal">
      Tooltip on Bottom
      <VTooltip
        activator="parent"
        location="bottom"
      >
        Bottom Tooltip
      </VTooltip>
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="tonal">
      Tooltip on End
      <VTooltip
        activator="parent"
        location="end"
      >
        End Tooltip
      </VTooltip>
    </VBtn>

    <VBtn variant="tonal">
      Tooltip on Start
      <VTooltip
        activator="parent"
        location="start"
      >
        Start Tooltip
      </VTooltip>
    </VBtn>

    <VBtn variant="tonal">
      Tooltip on Top
      <VTooltip
        activator="parent"
        location="top"
      >
        Top Tooltip
      </VTooltip>
    </VBtn>

    <VBtn variant="tonal">
      Tooltip on Bottom
      <VTooltip
        activator="parent"
        location="bottom"
      >
        Bottom Tooltip
      </VTooltip>
    </VBtn>
  </div>
</template>
`},so={ts:`<script setup lang="ts">
import avatar4 from '@images/avatars/avatar-4.png'
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn>
      Button
      <VTooltip
        location="top"
        activator="parent"
      >
        Tooltip
      </VTooltip>
    </VBtn>

    <VAvatar color="info">
      <VImg :src="avatar4" />
      <VTooltip
        location="top"
        activator="parent"
      >
        Tooltip on Avatar
      </VTooltip>
    </VAvatar>

    <VTooltip location="top">
      <template #activator="{ props }">
        <VIcon
          v-bind="props"
          size="30"
          icon="ri-user-line"
        />
      </template>
      <span>Tooltip on Icon</span>
    </VTooltip>
  </div>
</template>
`,js:`<script setup>
import avatar4 from '@images/avatars/avatar-4.png'
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn>
      Button
      <VTooltip
        location="top"
        activator="parent"
      >
        Tooltip
      </VTooltip>
    </VBtn>

    <VAvatar color="info">
      <VImg :src="avatar4" />
      <VTooltip
        location="top"
        activator="parent"
      >
        Tooltip on Avatar
      </VTooltip>
    </VAvatar>

    <VTooltip location="top">
      <template #activator="{ props }">
        <VIcon
          v-bind="props"
          size="30"
          icon="ri-user-line"
        />
      </template>
      <span>Tooltip on Icon</span>
    </VTooltip>
  </div>
</template>
`},ro={ts:`<template>
  <div class="demo-space-x">
    <!-- Scale transition -->
    <VBtn>
      scale transition
      <VTooltip
        location="top"
        transition="scale-transition"
        activator="parent"
      >
        <span>Scale Transition</span>
      </VTooltip>
    </VBtn>

    <!-- Scroll x transition -->
    <VBtn>
      scroll X transition
      <VTooltip
        location="top"
        activator="parent"
        transition="scroll-x-transition"
      >
        <span>Scroll X Transition</span>
      </VTooltip>
    </VBtn>

    <!-- Scroll y transition -->
    <VBtn>
      scroll y transition
      <VTooltip
        location="top"
        activator="parent"
        transition="scroll-y-transition"
      >
        <span>Scroll Y Transition</span>
      </VTooltip>
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <!-- Scale transition -->
    <VBtn>
      scale transition
      <VTooltip
        location="top"
        transition="scale-transition"
        activator="parent"
      >
        <span>Scale Transition</span>
      </VTooltip>
    </VBtn>

    <!-- Scroll x transition -->
    <VBtn>
      scroll X transition
      <VTooltip
        location="top"
        activator="parent"
        transition="scroll-x-transition"
      >
        <span>Scroll X Transition</span>
      </VTooltip>
    </VBtn>

    <!-- Scroll y transition -->
    <VBtn>
      scroll y transition
      <VTooltip
        location="top"
        activator="parent"
        transition="scroll-y-transition"
      >
        <span>Scroll Y Transition</span>
      </VTooltip>
    </VBtn>
  </div>
</template>
`},co={ts:`<script lang="ts" setup>
const isTooltipVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn @click="isTooltipVisible = !isTooltipVisible">
      toggle tooltip
    </VBtn>

    <VTooltip
      :model-value="isTooltipVisible"
      location="top"
    >
      <template #activator="{ props }">
        <VIcon
          v-bind="props"
          icon="ri-instagram-line"
        />
      </template>
      <span>Programmatic tooltip</span>
    </VTooltip>
  </div>
</template>
`,js:`<script setup>
const isTooltipVisible = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn @click="isTooltipVisible = !isTooltipVisible">
      toggle tooltip
    </VBtn>

    <VTooltip
      :model-value="isTooltipVisible"
      location="top"
    >
      <template #activator="{ props }">
        <VIcon
          v-bind="props"
          icon="ri-instagram-line"
        />
      </template>
      <span>Programmatic tooltip</span>
    </VTooltip>
  </div>
</template>
`},_o=a("p",null,[n("Use the "),a("code",null,"location"),n(" prop to specify on which side of the element the tooltip should show")],-1),vo=a("p",null,[n("Delay (in ms) after which tooltip opens (when "),a("code",null,"open-on-hover"),n(" prop is set to true)")],-1),Vo=a("p",null,[n("Tooltip visibility can be programmatically changed using "),a("code",null,"v-model"),n(".")],-1),uo=a("p",null,[n("Use "),a("code",null,"transition"),n(" prop to sets the component transition.")],-1),mo=a("p",null,"Tooltips can wrap any element.",-1),go={__name:"tooltip",setup(r){return(l,u)=>{const c=io,p=D,B=oo,O=q,h=N,x=M,g=I;return s(),f(b,null,{default:t(()=>[o(d,{cols:"12"},{default:t(()=>[o(p,{title:"Location",code:po},{default:t(()=>[_o,o(c)]),_:1},8,["code"])]),_:1}),o(d,{cols:"12"},{default:t(()=>[o(p,{title:"Events",code:lo},{default:t(()=>[o(B)]),_:1},8,["code"])]),_:1}),o(d,{cols:"12"},{default:t(()=>[o(p,{title:"Delay On Hover",code:eo},{default:t(()=>[vo,o(O)]),_:1},8,["code"])]),_:1}),o(d,{cols:"12"},{default:t(()=>[o(p,{title:"V-Model Support",code:co},{default:t(()=>[Vo,o(h)]),_:1},8,["code"])]),_:1}),o(d,{cols:"12"},{default:t(()=>[o(p,{title:"Transition",code:ro},{default:t(()=>[uo,o(x)]),_:1},8,["code"])]),_:1}),o(d,{cols:"12"},{default:t(()=>[o(p,{title:"Tooltip on Various Elements",code:so},{default:t(()=>[mo,o(g)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{go as default};
