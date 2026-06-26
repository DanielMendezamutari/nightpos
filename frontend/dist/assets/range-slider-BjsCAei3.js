import{aG as Z,cd as ee,bC as le,eH as ae,aJ as te,r as b,cf as se,eI as ne,bE as oe,eJ as ue,bF as ie,J as W,aL as re,bI as E,a,F as de,ar as ce,eK as me,eL as Y,an as pe,eM as G,o as g,h as S,d as k,y,w as d,n as ve,k as fe,i as R,b as c,e as v}from"./index-CzxvwHA0.js";import{_ as Ve}from"./AppCardCode-rKnCJYzZ.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-uvYrnk3q.js";import"./VCardText-CqJbZrdv.js";const _e=Z({...ee(),...le(),...ae(),strict:Boolean,modelValue:{type:Array,default:()=>[0,0]}},"VRangeSlider"),$=te()({name:"VRangeSlider",props:_e(),emits:{"update:focused":e=>!0,"update:modelValue":e=>!0,end:e=>!0,start:e=>!0},setup(e,s){let{slots:o,emit:n}=s;const l=b(),r=b(),h=b(),{rtlClasses:B}=se();function I(m){if(!l.value||!r.value)return;const p=G(m,l.value.$el,e.direction),i=G(m,r.value.$el,e.direction),u=Math.abs(p),f=Math.abs(i);return u<f||u===f&&p<0?l.value.$el:r.value.$el}const P=ne(e),t=oe(e,"modelValue",void 0,m=>m!=null&&m.length?m.map(p=>P.roundValue(p)):[0,0]),{activeThumbRef:V,hasLabels:H,max:j,min:M,mousePressed:K,onSliderMousedown:q,onSliderTouchstart:Q,position:L,trackContainerRef:X,readonly:z}=ue({props:e,steps:P,onSliderStart:()=>{n("start",t.value)},onSliderEnd:m=>{var u;let{value:p}=m;const i=V.value===((u=l.value)==null?void 0:u.$el)?[p,t.value[1]]:[t.value[0],p];!e.strict&&i[0]<i[1]&&(t.value=i),n("end",t.value)},onSliderMove:m=>{var f,x,w,_;let{value:p}=m;const[i,u]=t.value;!e.strict&&i===u&&i!==M.value&&(V.value=p>i?(f=r.value)==null?void 0:f.$el:(x=l.value)==null?void 0:x.$el,(w=V.value)==null||w.focus()),V.value===((_=l.value)==null?void 0:_.$el)?t.value=[Math.min(p,u),u]:t.value=[i,Math.max(i,p)]},getActiveThumb:I}),{isFocused:U,focus:A,blur:J}=ie(e),N=W(()=>L(t.value[0])),O=W(()=>L(t.value[1]));return re(()=>{const m=E.filterProps(e),p=!!(e.label||o.label||o.prepend);return a(E,pe({class:["v-slider","v-range-slider",{"v-slider--has-labels":!!o["tick-label"]||H.value,"v-slider--focused":U.value,"v-slider--pressed":K.value,"v-slider--disabled":e.disabled},B.value,e.class],style:e.style,ref:h},m,{focused:U.value}),{...o,prepend:p?i=>{var u,f;return a(de,null,[((u=o.label)==null?void 0:u.call(o,i))??(e.label?a(ce,{class:"v-slider__label",text:e.label},null):void 0),(f=o.prepend)==null?void 0:f.call(o,i)])}:void 0,default:i=>{var x,w;let{id:u,messagesId:f}=i;return a("div",{class:"v-slider__container",onMousedown:z.value?void 0:q,onTouchstartPassive:z.value?void 0:Q},[a("input",{id:`${u.value}_start`,name:e.name||u.value,disabled:!!e.disabled,readonly:!!e.readonly,tabindex:"-1",value:t.value[0]},null),a("input",{id:`${u.value}_stop`,name:e.name||u.value,disabled:!!e.disabled,readonly:!!e.readonly,tabindex:"-1",value:t.value[1]},null),a(me,{ref:X,start:N.value,stop:O.value},{"tick-label":o["tick-label"]}),a(Y,{ref:l,"aria-describedby":f.value,focused:U&&V.value===((x=l.value)==null?void 0:x.$el),modelValue:t.value[0],"onUpdate:modelValue":_=>t.value=[_,t.value[1]],onFocus:_=>{var D,T,F,C;A(),V.value=(D=l.value)==null?void 0:D.$el,t.value[0]===t.value[1]&&t.value[1]===M.value&&_.relatedTarget!==((T=r.value)==null?void 0:T.$el)&&((F=l.value)==null||F.$el.blur(),(C=r.value)==null||C.$el.focus())},onBlur:()=>{J(),V.value=void 0},min:M.value,max:t.value[1],position:N.value,ripple:e.ripple},{"thumb-label":o["thumb-label"]}),a(Y,{ref:r,"aria-describedby":f.value,focused:U&&V.value===((w=r.value)==null?void 0:w.$el),modelValue:t.value[1],"onUpdate:modelValue":_=>t.value=[t.value[0],_],onFocus:_=>{var D,T,F,C;A(),V.value=(D=r.value)==null?void 0:D.$el,t.value[0]===t.value[1]&&t.value[0]===j.value&&_.relatedTarget!==((T=l.value)==null?void 0:T.$el)&&((F=r.value)==null||F.$el.blur(),(C=l.value)==null||C.$el.focus())},onBlur:()=>{J(),V.value=void 0},min:t.value[0],max:j.value,position:O.value,ripple:e.ripple},{"thumb-label":o["thumb-label"]})])}})}),{}}}),be={__name:"DemoRangeSliderVertical",setup(e){const s=b([20,40]);return(o,n)=>(g(),S($,{modelValue:k(s),"onUpdate:modelValue":n[0]||(n[0]=l=>y(s)?s.value=l:null),direction:"vertical"},null,8,["modelValue"]))}},he={__name:"DemoRangeSliderThumbLabel",setup(e){const s=["Winter","Spring","Summer","Fall"],o=["ri-snowy-line","ri-leaf-line","ri-fire-line","ri-drop-line"],n=b([1,2]);return(l,r)=>(g(),S($,{modelValue:k(n),"onUpdate:modelValue":r[0]||(r[0]=h=>y(n)?n.value=h:null),tick:s,min:"0",max:"3",step:1,"show-ticks":"always","thumb-label":"","tick-size":"4"},{"thumb-label":d(({modelValue:h})=>[a(ve,{icon:o[h]},null,8,["icon"])]),_:1},8,["modelValue"]))}},ge={__name:"DemoRangeSliderStep",setup(e){const s=b([20,40]);return(o,n)=>(g(),S($,{modelValue:k(s),"onUpdate:modelValue":n[0]||(n[0]=l=>y(s)?s.value=l:null),step:"10"},null,8,["modelValue"]))}},Se={__name:"DemoRangeSliderColor",setup(e){const s=b([10,60]);return(o,n)=>(g(),S($,{modelValue:k(s),"onUpdate:modelValue":n[0]||(n[0]=l=>y(s)?s.value=l:null),color:"success","track-color":"secondary"},null,8,["modelValue"]))}},Re={__name:"DemoRangeSliderDisabled",setup(e){const s=b([30,60]);return(o,n)=>(g(),S($,{modelValue:k(s),"onUpdate:modelValue":n[0]||(n[0]=l=>y(s)?s.value=l:null),disabled:"",label:"Disabled"},null,8,["modelValue"]))}},ke={__name:"DemoRangeSliderBasic",setup(e){const s=b([10,60]);return(o,n)=>(g(),S($,{modelValue:k(s),"onUpdate:modelValue":n[0]||(n[0]=l=>y(s)?s.value=l:null)},null,8,["modelValue"]))}},ye={ts:`<script setup lang="ts">
const sliderValues = ref([10, 60])
<\/script>

<template>
  <VRangeSlider v-model="sliderValues" />
</template>
`,js:`<script setup>
const sliderValues = ref([
  10,
  60,
])
<\/script>

<template>
  <VRangeSlider v-model="sliderValues" />
</template>
`},$e={ts:`<script lang="ts" setup>
const sliderValues = ref([10, 60])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    color="success"
    track-color="secondary"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  10,
  60,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    color="success"
    track-color="secondary"
  />
</template>
`},xe={ts:`<script lang="ts" setup>
const slidersValues = ref([30, 60])
<\/script>

<template>
  <VRangeSlider
    v-model="slidersValues"
    disabled
    label="Disabled"
  />
</template>
`,js:`<script setup>
const slidersValues = ref([
  30,
  60,
])
<\/script>

<template>
  <VRangeSlider
    v-model="slidersValues"
    disabled
    label="Disabled"
  />
</template>
`},we={ts:`<script lang="ts" setup>
const sliderValues = ref([20, 40])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    step="10"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  20,
  40,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    step="10"
  />
</template>
`},De={ts:`<script lang="ts" setup>
const seasons = ['Winter', 'Spring', 'Summer', 'Fall']
const icons = ['ri-snowy-line', 'ri-leaf-line', 'ri-fire-line', 'ri-drop-line']
const sliderValues = ref([1, 2])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    :tick="seasons"
    min="0"
    max="3"
    :step="1"
    show-ticks="always"
    thumb-label
    tick-size="4"
  >
    <template #thumb-label="{ modelValue }">
      <VIcon :icon="icons[modelValue]" />
    </template>
  </VRangeSlider>
</template>
`,js:`<script setup>
const seasons = [
  'Winter',
  'Spring',
  'Summer',
  'Fall',
]

const icons = [
  'ri-snowy-line',
  'ri-leaf-line',
  'ri-fire-line',
  'ri-drop-line',
]

const sliderValues = ref([
  1,
  2,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    :tick="seasons"
    min="0"
    max="3"
    :step="1"
    show-ticks="always"
    thumb-label
    tick-size="4"
  >
    <template #thumb-label="{ modelValue }">
      <VIcon :icon="icons[modelValue]" />
    </template>
  </VRangeSlider>
</template>
`},Te={ts:`<script lang="ts" setup>
const sliderValues = ref([20, 40])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    direction="vertical"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  20,
  40,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    direction="vertical"
  />
</template>
`},Fe=c("p",null,[v("The "),c("code",null,"v-slider"),v(" component is a better visualization of the number input.")],-1),Ce=c("p",null,[v("You cannot interact with "),c("code",null,"disabled"),v(" sliders.")],-1),Pe=c("p",null,[v("Use "),c("code",null,"color"),v(" prop to the sets the slider color. "),c("code",null,"track-color"),v(" prop to sets the color of slider's unfilled track.")],-1),Ue=c("p",null,[c("code",null,"v-range-slider"),v(" can have steps other than 1. This can be helpful for some applications where you need to adjust values with more or less accuracy.")],-1),Be=c("p",null,[v(" Using the "),c("code",null,"tick-labels"),v(" prop along with the "),c("code",null,"thumb-label"),v(" slot, you can create a very customized solution. ")],-1),Ie=c("p",null,[v("You can use the "),c("code",null,"vertical"),v(" prop to switch sliders to a vertical orientation. If you need to change the height of the slider, use css.")],-1),Je={__name:"range-slider",setup(e){return(s,o)=>{const n=ke,l=Ve,r=Re,h=Se,B=ge,I=he,P=be;return g(),S(fe,null,{default:d(()=>[a(R,{cols:"12",md:"6"},{default:d(()=>[a(l,{title:"Basic",code:ye},{default:d(()=>[Fe,a(n)]),_:1},8,["code"])]),_:1}),a(R,{cols:"12",md:"6"},{default:d(()=>[a(l,{title:"Disabled",code:xe},{default:d(()=>[Ce,a(r)]),_:1},8,["code"])]),_:1}),a(R,{cols:"12",md:"6"},{default:d(()=>[a(l,{title:"Color",code:$e},{default:d(()=>[Pe,a(h)]),_:1},8,["code"])]),_:1}),a(R,{cols:"12",md:"6"},{default:d(()=>[a(l,{title:"Step",code:we},{default:d(()=>[Ue,a(B)]),_:1},8,["code"])]),_:1}),a(R,{cols:"12",md:"6"},{default:d(()=>[a(l,{title:"Thumb label",code:De},{default:d(()=>[Be,a(I)]),_:1},8,["code"])]),_:1}),a(R,{cols:"12",md:"6"},{default:d(()=>[a(l,{title:"Vertical",code:Te},{default:d(()=>[Ie,a(P)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{Je as default};
