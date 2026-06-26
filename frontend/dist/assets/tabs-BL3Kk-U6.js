import{a as l,V as T}from"./VTabs-DalVSQfk.js";import{r as h,M as N,o as u,c as d,a as e,w as a,F as p,y as w,d as m,g as k,e as s,t as g,x as b,b as o,f as q,_ as W,m as x,j as A,h as _}from"./index-UQ56zYDF.js";import{a as f,V as I}from"./VWindowItem-C6FRJSRU.js";import{_ as z}from"./AppCardCode-B7j3Avkk.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-CSxl8Spr.js";import"./VCardText-G2x92PbA.js";const O={class:"text-center mt-9"},U={__name:"DemoTabsDynamic",setup(V){const t=h(3),c=h(0);return N(t,i=>{c.value=i-1}),(i,n)=>(u(),d(p,null,[e(T,{modelValue:m(c),"onUpdate:modelValue":n[0]||(n[0]=r=>b(c)?c.value=r:null)},{default:a(()=>[(u(!0),d(p,null,w(m(t),r=>(u(),k(l,{key:r,value:r},{default:a(()=>[s(" Tab "+g(r),1)]),_:2},1032,["value"]))),128))]),_:1},8,["modelValue"]),o("div",O,[e(q,{disabled:!m(t),variant:"text",onClick:n[1]||(n[1]=r=>t.value--)},{default:a(()=>[s(" Remove Tab ")]),_:1},8,["disabled"]),e(q,{variant:"text",onClick:n[2]||(n[2]=r=>t.value++)},{default:a(()=>[s(" Add Tab ")]),_:1})])],64))}},M={class:"text-center"},E="Chocolate cake marshmallow toffee sweet caramels tootsie roll chocolate bar. Chocolate candy lemon drops cupcake macaroon liquorice. Icing tiramisu cake pastry jujubes lollipop gummies sugar plum pie.",H={__name:"DemoTabsProgrammaticNavigation",setup(V){const t=h(1),c=["Appetizers","Entrees","Deserts","Cocktails"],i=c.length,n=()=>{t.value!==1&&(t.value-=1)},r=()=>{t.value!==i&&(t.value+=1)};return(C,y)=>(u(),d(p,null,[e(T,{modelValue:m(t),"onUpdate:modelValue":y[0]||(y[0]=v=>b(t)?t.value=v:null),grow:""},{default:a(()=>[(u(!0),d(p,null,w(c.length,v=>(u(),k(l,{key:v,value:v},{default:a(()=>[s(g(c[v-1]),1)]),_:2},1032,["value"]))),128))]),_:1},8,["modelValue"]),e(I,{modelValue:m(t),"onUpdate:modelValue":y[1]||(y[1]=v=>b(t)?t.value=v:null),class:"mt-5"},{default:a(()=>[(u(!0),d(p,null,w(c.length,v=>(u(),k(f,{key:v,value:v},{default:a(()=>[s(g(E))]),_:2},1032,["value"]))),128))]),_:1},8,["modelValue"]),o("div",M,[e(q,{variant:"text",disabled:m(t)===1,onClick:n},{default:a(()=>[s(" Previous ")]),_:1},8,["disabled"]),e(q,{variant:"text",disabled:m(t)===m(i),onClick:r},{default:a(()=>[s(" Next ")]),_:1},8,["disabled"])])],64))}},R="hortbread chocolate bar marshmallow bear claw tiramisu chocolate cookie wafer. Gummies sweet brownie brownie marshmallow chocolate cake pastry. Topping macaroon shortbread liquorice dragée macaroon.",G={__name:"DemoTabsGrow",setup(V){const t=h("Appetizers"),c=["Appetizers","Entrees","Deserts","Cocktails"];return(i,n)=>(u(),d(p,null,[e(T,{modelValue:m(t),"onUpdate:modelValue":n[0]||(n[0]=r=>b(t)?t.value=r:null),grow:""},{default:a(()=>[(u(),d(p,null,w(c,r=>e(l,{key:r,value:r},{default:a(()=>[s(g(r),1)]),_:2},1032,["value"])),64))]),_:1},8,["modelValue"]),e(I,{modelValue:m(t),"onUpdate:modelValue":n[1]||(n[1]=r=>b(t)?t.value=r:null),class:"mt-6"},{default:a(()=>[(u(),d(p,null,w(c,r=>e(f,{key:r,value:r},{default:a(()=>[s(g(R))]),_:2},1032,["value"])),64))]),_:1},8,["modelValue"])],64))}},L={};function J(V,t){return u(),k(T,{"fixed-tabs":""},{default:a(()=>[e(l,null,{default:a(()=>[s(" Fixed Tab 1 ")]),_:1}),e(l,null,{default:a(()=>[s(" Fixed Tab 2 ")]),_:1}),e(l,null,{default:a(()=>[s(" Fixed Tab 3 ")]),_:1}),e(l,null,{default:a(()=>[s(" Fixed Tab 4 ")]),_:1})]),_:1})}const K=W(L,[["render",J]]),Q={};function X(V,t){return u(),k(T,{"next-icon":"ri-arrow-right-line","prev-icon":"ri-arrow-left-line","show-arrows":""},{default:a(()=>[(u(),d(p,null,w(10,c=>e(l,{key:c},{default:a(()=>[s(" Item "+g(c),1)]),_:2},1024)),64))]),_:1})}const Y=W(Q,[["render",X]]),Z={};function ee(V,t){return u(),k(T,{"show-arrows":""},{default:a(()=>[(u(),d(p,null,w(10,c=>e(l,{key:c,value:c},{default:a(()=>[s(" Item "+g(c),1)]),_:2},1032,["value"])),64))]),_:1})}const ae=W(Z,[["render",ee]]),te={};function se(V,t){return u(),d(p,null,[e(T,null,{default:a(()=>[e(l,null,{default:a(()=>[s("Home")]),_:1}),e(l,null,{default:a(()=>[s("Service")]),_:1}),e(l,null,{default:a(()=>[s("Account")]),_:1})]),_:1}),e(T,{"align-tabs":"center"},{default:a(()=>[e(l,null,{default:a(()=>[s("Home")]),_:1}),e(l,null,{default:a(()=>[s("Service")]),_:1}),e(l,null,{default:a(()=>[s("Account")]),_:1})]),_:1}),e(T,{"align-tabs":"end"},{default:a(()=>[e(l,null,{default:a(()=>[s("Home")]),_:1}),e(l,null,{default:a(()=>[s("Service")]),_:1}),e(l,null,{default:a(()=>[s("Account")]),_:1})]),_:1})],64)}const ne=W(te,[["render",se]]),oe={class:"d-flex gap-6"},ie=o("p",null," Sed aliquam ultrices mauris. Donec posuere vulputate arcu. Morbi ac felis. Etiam feugiat lorem non metus. Sed a libero. ",-1),le=o("p",{class:"mb-0"}," Phasellus dolor. Fusce neque. Fusce fermentum odio nec arcu. Pellentesque libero tortor, tincidunt et. ",-1),ue=o("p",{class:"mb-0"}," Morbi nec metus. Suspendisse faucibus, nunc et pellentesque egestas, lacus ante convallis tellus, vitae iaculis lacus elit id tortor. Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo. Nunc sed turpis. ",-1),ce=o("p",{class:"mb-0"}," Fusce a quam. Phasellus nec sem in justo pellentesque facilisis. Nam eget dui. Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. In dui magna, posuere eget, vestibulum et, tempor auctor, justo. ",-1),re={__name:"DemoTabsVerticalPill",setup(V){const t=h("window-1");return(c,i)=>(u(),d("div",oe,[o("div",null,[e(T,{modelValue:m(t),"onUpdate:modelValue":i[0]||(i[0]=n=>b(t)?t.value=n:null),direction:"vertical",class:"v-tabs-pill"},{default:a(()=>[e(l,null,{default:a(()=>[e(x,{start:"",icon:"ri-user-line"}),s(" Option 1 ")]),_:1}),e(l,null,{default:a(()=>[e(x,{start:"",icon:"ri-lock-line"}),s(" Option 2 ")]),_:1}),e(l,null,{default:a(()=>[e(x,{start:"",icon:"ri-rfid-line"}),s(" Option 3 ")]),_:1})]),_:1},8,["modelValue"])]),e(I,{modelValue:m(t),"onUpdate:modelValue":i[1]||(i[1]=n=>b(t)?t.value=n:null)},{default:a(()=>[e(f,{value:"window-1"},{default:a(()=>[ie,le]),_:1}),e(f,{value:"window-2"},{default:a(()=>[ue]),_:1}),e(f,{value:"window-3"},{default:a(()=>[ce]),_:1})]),_:1},8,["modelValue"])]))}},me={class:"d-flex"},de=o("p",null," Sed aliquam ultrices mauris. Donec posuere vulputate arcu. Morbi ac felis. Etiam feugiat lorem non metus. Sed a libero. ",-1),pe=o("p",{class:"mb-0"}," Phasellus dolor. Fusce neque. Fusce fermentum odio nec arcu. Pellentesque libero tortor, tincidunt et, tincidunt eget, semper nec, quam. Phasellus blandit leo ut odio. ",-1),be=o("p",null," Morbi nec metus. Suspendisse faucibus, nunc et pellentesque egestas, lacus ante convallis tellus, vitae iaculis lacus elit id tortor. Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo. Nunc sed turpis. ",-1),Te=o("p",{class:"mb-0"}," Donec venenatis vulputate lorem. Aenean viverra rhoncus pede. In dui magna, posuere eget, vestibulum et, tempor auctor, justo. Fusce commodo aliquam arcu. Suspendisse enim turpis, dictum sed, iaculis a, condimentum nec, nisi. ",-1),Ve=o("p",null," Fusce a quam. Phasellus nec sem in justo pellentesque facilisis. Nam eget dui. Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. In dui magna, posuere eget, vestibulum et, tempor auctor, justo. ",-1),ve=o("p",{class:"mb-0"}," Cras sagittis. Phasellus nec sem in justo pellentesque facilisis. Proin sapien ipsum, porta a, auctor quis, euismod ut, mi. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nam at tortor in tellus interdum sagittis. ",-1),_e={__name:"DemoTabsVertical",setup(V){const t=h(0);return(c,i)=>(u(),d("div",me,[e(T,{modelValue:m(t),"onUpdate:modelValue":i[0]||(i[0]=n=>b(t)?t.value=n:null),direction:"vertical"},{default:a(()=>[e(l,{"prepend-icon":"ri-user-line"},{default:a(()=>[s(" Option 1 ")]),_:1}),e(l,{"prepend-icon":"ri-lock-line"},{default:a(()=>[s(" Option 2 ")]),_:1}),e(l,{"prepend-icon":"ri-rfid-line"},{default:a(()=>[s(" Option 3 ")]),_:1})]),_:1},8,["modelValue"]),e(I,{modelValue:m(t),"onUpdate:modelValue":i[1]||(i[1]=n=>b(t)?t.value=n:null),class:"ms-3"},{default:a(()=>[e(f,null,{default:a(()=>[de,pe]),_:1}),e(f,null,{default:a(()=>[be,Te]),_:1}),e(f,null,{default:a(()=>[Ve,ve]),_:1})]),_:1},8,["modelValue"])]))}},fe=o("span",null,"Recent",-1),we=o("span",null,"Favorites",-1),ge=o("span",null,"Nearby",-1),he="Biscuit cheesecake gingerbread oat cake tiramisu. Marzipan tiramisu jelly-o muffin biscuit jelly cake pie. Chocolate cookie candy croissant brownie cupcake powder cheesecake. Biscuit sesame snaps biscuit topping tiramisu croissant.",ke={__name:"DemoTabsStacked",setup(V){const t=h("tab-1");return(c,i)=>(u(),d(p,null,[e(T,{modelValue:m(t),"onUpdate:modelValue":i[0]||(i[0]=n=>b(t)?t.value=n:null),grow:"",stacked:""},{default:a(()=>[e(l,{value:"tab-1"},{default:a(()=>[e(x,{icon:"ri-phone-line",class:"mb-2"}),fe]),_:1}),e(l,{value:"tab-2"},{default:a(()=>[e(x,{icon:"ri-heart-line",class:"mb-2"}),we]),_:1}),e(l,{value:"tab-3"},{default:a(()=>[e(x,{icon:"ri-account-box-line",class:"mb-2"}),ge]),_:1})]),_:1},8,["modelValue"]),e(I,{modelValue:m(t),"onUpdate:modelValue":i[1]||(i[1]=n=>b(t)?t.value=n:null),class:"mt-5"},{default:a(()=>[(u(),d(p,null,w(3,n=>e(f,{key:n,value:`tab-${n}`},{default:a(()=>[s(g(he))]),_:2},1032,["value"])),64))]),_:1},8,["modelValue"])],64))}},Ie="Candy canes donut chupa chups candy canes lemon drops oat cake wafer. Cotton candy candy canes marzipan carrot cake. Sesame snaps lemon drops candy marzipan donut brownie tootsie roll. Icing croissant bonbon biscuit gummi bears. Pudding candy canes sugar plum cookie chocolate cake powder croissant.",ye={__name:"DemoTabsBasicPill",setup(V){const t=h(0);return(c,i)=>(u(),d(p,null,[e(T,{modelValue:m(t),"onUpdate:modelValue":i[0]||(i[0]=n=>b(t)?t.value=n:null),class:"v-tabs-pill"},{default:a(()=>[e(l,null,{default:a(()=>[s("Tab One")]),_:1}),e(l,null,{default:a(()=>[s("Tab Two")]),_:1}),e(l,null,{default:a(()=>[s("Tab Three")]),_:1})]),_:1},8,["modelValue"]),e(I,{modelValue:m(t),"onUpdate:modelValue":i[1]||(i[1]=n=>b(t)?t.value=n:null),class:"mt-5"},{default:a(()=>[(u(),d(p,null,w(3,n=>e(f,{key:n},{default:a(()=>[s(g(Ie))]),_:2},1024)),64))]),_:1},8,["modelValue"])],64))}},xe="Candy canes donut chupa chups candy canes lemon drops oat cake wafer. Cotton candy candy canes marzipan carrot cake. Sesame snaps lemon drops candy marzipan donut brownie tootsie roll. Icing croissant bonbon biscuit gummi bears. Pudding candy canes sugar plum cookie chocolate cake powder croissant.",qe={__name:"DemoTabsBasic",setup(V){const t=h(0);return(c,i)=>(u(),d(p,null,[e(T,{modelValue:m(t),"onUpdate:modelValue":i[0]||(i[0]=n=>b(t)?t.value=n:null)},{default:a(()=>[e(l,null,{default:a(()=>[s("Tab One")]),_:1}),e(l,null,{default:a(()=>[s("Tab Two")]),_:1}),e(l,null,{default:a(()=>[s("Tab Three")]),_:1})]),_:1},8,["modelValue"]),e(I,{modelValue:m(t),"onUpdate:modelValue":i[1]||(i[1]=n=>b(t)?t.value=n:null),class:"mt-5"},{default:a(()=>[(u(),d(p,null,w(3,n=>e(f,{key:n},{default:a(()=>[s(g(xe))]),_:2},1024)),64))]),_:1},8,["modelValue"])],64))}},We={ts:`<template>
  <!-- Default -->
  <VTabs>
    <VTab>Home</VTab>
    <VTab>Service</VTab>
    <VTab>Account</VTab>
  </VTabs>

  <!-- Center -->
  <VTabs align-tabs="center">
    <VTab>Home</VTab>
    <VTab>Service</VTab>
    <VTab>Account</VTab>
  </VTabs>

  <!-- End -->
  <VTabs align-tabs="end">
    <VTab>Home</VTab>
    <VTab>Service</VTab>
    <VTab>Account</VTab>
  </VTabs>
</template>
`,js:`<template>
  <!-- Default -->
  <VTabs>
    <VTab>Home</VTab>
    <VTab>Service</VTab>
    <VTab>Account</VTab>
  </VTabs>

  <!-- Center -->
  <VTabs align-tabs="center">
    <VTab>Home</VTab>
    <VTab>Service</VTab>
    <VTab>Account</VTab>
  </VTabs>

  <!-- End -->
  <VTabs align-tabs="end">
    <VTab>Home</VTab>
    <VTab>Service</VTab>
    <VTab>Account</VTab>
  </VTabs>
</template>
`},Ce={ts:`<script lang="ts" setup>
const currentTab = ref(0)
const tabItemContent = 'Candy canes donut chupa chups candy canes lemon drops oat cake wafer. Cotton candy candy canes marzipan carrot cake. Sesame snaps lemon drops candy marzipan donut brownie tootsie roll. Icing croissant bonbon biscuit gummi bears. Pudding candy canes sugar plum cookie chocolate cake powder croissant.'
<\/script>

<template>
  <VTabs v-model="currentTab">
    <VTab>Tab One</VTab>
    <VTab>Tab Two</VTab>
    <VTab>Tab Three</VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="item in 3"
      :key="item"
    >
      {{ tabItemContent }}
    </VWindowItem>
  </VWindow>
</template>
`,js:`<script setup>
const currentTab = ref(0)
const tabItemContent = 'Candy canes donut chupa chups candy canes lemon drops oat cake wafer. Cotton candy candy canes marzipan carrot cake. Sesame snaps lemon drops candy marzipan donut brownie tootsie roll. Icing croissant bonbon biscuit gummi bears. Pudding candy canes sugar plum cookie chocolate cake powder croissant.'
<\/script>

<template>
  <VTabs v-model="currentTab">
    <VTab>Tab One</VTab>
    <VTab>Tab Two</VTab>
    <VTab>Tab Three</VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="item in 3"
      :key="item"
    >
      {{ tabItemContent }}
    </VWindowItem>
  </VWindow>
</template>
`},Pe={ts:`<script lang="ts" setup>
const currentTab = ref(0)
const tabItemContent = 'Candy canes donut chupa chups candy canes lemon drops oat cake wafer. Cotton candy candy canes marzipan carrot cake. Sesame snaps lemon drops candy marzipan donut brownie tootsie roll. Icing croissant bonbon biscuit gummi bears. Pudding candy canes sugar plum cookie chocolate cake powder croissant.'
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    class="v-tabs-pill"
  >
    <VTab>Tab One</VTab>
    <VTab>Tab Two</VTab>
    <VTab>Tab Three</VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="item in 3"
      :key="item"
    >
      {{ tabItemContent }}
    </VWindowItem>
  </VWindow>
</template>
`,js:`<script setup>
const currentTab = ref(0)
const tabItemContent = 'Candy canes donut chupa chups candy canes lemon drops oat cake wafer. Cotton candy candy canes marzipan carrot cake. Sesame snaps lemon drops candy marzipan donut brownie tootsie roll. Icing croissant bonbon biscuit gummi bears. Pudding candy canes sugar plum cookie chocolate cake powder croissant.'
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    class="v-tabs-pill"
  >
    <VTab>Tab One</VTab>
    <VTab>Tab Two</VTab>
    <VTab>Tab Three</VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="item in 3"
      :key="item"
    >
      {{ tabItemContent }}
    </VWindowItem>
  </VWindow>
</template>
`},Se={ts:`<template>
  <VTabs
    next-icon="ri-arrow-right-line"
    prev-icon="ri-arrow-left-line"
    show-arrows
  >
    <VTab
      v-for="i in 10"
      :key="i"
    >
      Item {{ i }}
    </VTab>
  </VTabs>
</template>
`,js:`<template>
  <VTabs
    next-icon="ri-arrow-right-line"
    prev-icon="ri-arrow-left-line"
    show-arrows
  >
    <VTab
      v-for="i in 10"
      :key="i"
    >
      Item {{ i }}
    </VTab>
  </VTabs>
</template>
`},De={ts:`<script lang="ts" setup>
const totalTabs = ref(3)
const currentTab = ref(0)

watch(totalTabs, newValue => {
  currentTab.value = newValue - 1
})
<\/script>

<template>
  <VTabs v-model="currentTab">
    <VTab
      v-for="n in totalTabs"
      :key="n"
      :value="n"
    >
      Tab {{ n }}
    </VTab>
  </VTabs>

  <!-- buttons -->
  <div class="text-center mt-9">
    <VBtn
      :disabled="!totalTabs"
      variant="text"
      @click="totalTabs--"
    >
      Remove Tab
    </VBtn>

    <VBtn
      variant="text"
      @click="totalTabs++"
    >
      Add Tab
    </VBtn>
  </div>
</template>
`,js:`<script setup>
const totalTabs = ref(3)
const currentTab = ref(0)

watch(totalTabs, newValue => {
  currentTab.value = newValue - 1
})
<\/script>

<template>
  <VTabs v-model="currentTab">
    <VTab
      v-for="n in totalTabs"
      :key="n"
      :value="n"
    >
      Tab {{ n }}
    </VTab>
  </VTabs>

  <!-- buttons -->
  <div class="text-center mt-9">
    <VBtn
      :disabled="!totalTabs"
      variant="text"
      @click="totalTabs--"
    >
      Remove Tab
    </VBtn>

    <VBtn
      variant="text"
      @click="totalTabs++"
    >
      Add Tab
    </VBtn>
  </div>
</template>
`},je={ts:`<template>
  <VTabs fixed-tabs>
    <VTab>
      Fixed Tab 1
    </VTab>
    <VTab>
      Fixed Tab 2
    </VTab>
    <VTab>
      Fixed Tab 3
    </VTab>
    <VTab>
      Fixed Tab 4
    </VTab>
  </VTabs>
</template>
`,js:`<template>
  <VTabs fixed-tabs>
    <VTab>
      Fixed Tab 1
    </VTab>
    <VTab>
      Fixed Tab 2
    </VTab>
    <VTab>
      Fixed Tab 3
    </VTab>
    <VTab>
      Fixed Tab 4
    </VTab>
  </VTabs>
</template>
`},$e={ts:`<script lang="ts" setup>
const currentTab = ref('Appetizers')
const items = ['Appetizers', 'Entrees', 'Deserts', 'Cocktails']
const tabItemText = 'hortbread chocolate bar marshmallow bear claw tiramisu chocolate cookie wafer. Gummies sweet brownie brownie marshmallow chocolate cake pastry. Topping macaroon shortbread liquorice dragée macaroon.'
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    grow
  >
    <VTab
      v-for="item in items"
      :key="item"
      :value="item"
    >
      {{ item }}
    </VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-6"
  >
    <VWindowItem
      v-for="item in items"
      :key="item"
      :value="item"
    >
      {{ tabItemText }}
    </VWindowItem>
  </VWindow>
</template>
`,js:`<script setup>
const currentTab = ref('Appetizers')

const items = [
  'Appetizers',
  'Entrees',
  'Deserts',
  'Cocktails',
]

const tabItemText = 'hortbread chocolate bar marshmallow bear claw tiramisu chocolate cookie wafer. Gummies sweet brownie brownie marshmallow chocolate cake pastry. Topping macaroon shortbread liquorice dragée macaroon.'
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    grow
  >
    <VTab
      v-for="item in items"
      :key="item"
      :value="item"
    >
      {{ item }}
    </VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-6"
  >
    <VWindowItem
      v-for="item in items"
      :key="item"
      :value="item"
    >
      {{ tabItemText }}
    </VWindowItem>
  </VWindow>
</template>
`},Fe={ts:`<template>
  <VTabs show-arrows>
    <VTab
      v-for="i in 10"
      :key="i"
      :value="i"
    >
      Item {{ i }}
    </VTab>
  </VTabs>
</template>
`,js:`<template>
  <VTabs show-arrows>
    <VTab
      v-for="i in 10"
      :key="i"
      :value="i"
    >
      Item {{ i }}
    </VTab>
  </VTabs>
</template>
`},Be={ts:`<script lang="ts" setup>
const currentTab = ref(1)
const items = ['Appetizers', 'Entrees', 'Deserts', 'Cocktails']
const tabItemText = 'Chocolate cake marshmallow toffee sweet caramels tootsie roll chocolate bar. Chocolate candy lemon drops cupcake macaroon liquorice. Icing tiramisu cake pastry jujubes lollipop gummies sugar plum pie.'
const totalTabs = items.length

const preTab = () => {
  if (currentTab.value !== 1)
    currentTab.value -= 1
}

const nextTab = () => {
  if (currentTab.value !== totalTabs)
    currentTab.value += 1
}
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    grow
  >
    <VTab
      v-for="item in items.length"
      :key="item"
      :value="item"
    >
      {{ items[item - 1] }}
    </VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="item in items.length"
      :key="item"
      :value="item"
    >
      {{ tabItemText }}
    </VWindowItem>
  </VWindow>

  <div class="text-center">
    <VBtn
      variant="text"
      :disabled="currentTab === 1"
      @click="preTab"
    >
      Previous
    </VBtn>

    <VBtn
      variant="text"
      :disabled="currentTab === totalTabs"
      @click="nextTab"
    >
      Next
    </VBtn>
  </div>
</template>
`,js:`<script setup>
const currentTab = ref(1)

const items = [
  'Appetizers',
  'Entrees',
  'Deserts',
  'Cocktails',
]

const tabItemText = 'Chocolate cake marshmallow toffee sweet caramels tootsie roll chocolate bar. Chocolate candy lemon drops cupcake macaroon liquorice. Icing tiramisu cake pastry jujubes lollipop gummies sugar plum pie.'
const totalTabs = items.length

const preTab = () => {
  if (currentTab.value !== 1)
    currentTab.value -= 1
}

const nextTab = () => {
  if (currentTab.value !== totalTabs)
    currentTab.value += 1
}
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    grow
  >
    <VTab
      v-for="item in items.length"
      :key="item"
      :value="item"
    >
      {{ items[item - 1] }}
    </VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="item in items.length"
      :key="item"
      :value="item"
    >
      {{ tabItemText }}
    </VWindowItem>
  </VWindow>

  <div class="text-center">
    <VBtn
      variant="text"
      :disabled="currentTab === 1"
      @click="preTab"
    >
      Previous
    </VBtn>

    <VBtn
      variant="text"
      :disabled="currentTab === totalTabs"
      @click="nextTab"
    >
      Next
    </VBtn>
  </div>
</template>
`},Ne={ts:`<script lang="ts" setup>
const currentTab = ref('tab-1')
const tabItemText = 'Biscuit cheesecake gingerbread oat cake tiramisu. Marzipan tiramisu jelly-o muffin biscuit jelly cake pie. Chocolate cookie candy croissant brownie cupcake powder cheesecake. Biscuit sesame snaps biscuit topping tiramisu croissant.'
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    grow
    stacked
  >
    <VTab value="tab-1">
      <VIcon
        icon="ri-phone-line"
        class="mb-2"
      />
      <span>Recent</span>
    </VTab>

    <VTab value="tab-2">
      <VIcon
        icon="ri-heart-line"
        class="mb-2"
      />
      <span>Favorites</span>
    </VTab>

    <VTab value="tab-3">
      <VIcon
        icon="ri-account-box-line"
        class="mb-2"
      />
      <span>Nearby</span>
    </VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="i in 3"
      :key="i"
      :value="\`tab-\${i}\`"
    >
      {{ tabItemText }}
    </VWindowItem>
  </VWindow>
</template>
`,js:`<script setup>
const currentTab = ref('tab-1')
const tabItemText = 'Biscuit cheesecake gingerbread oat cake tiramisu. Marzipan tiramisu jelly-o muffin biscuit jelly cake pie. Chocolate cookie candy croissant brownie cupcake powder cheesecake. Biscuit sesame snaps biscuit topping tiramisu croissant.'
<\/script>

<template>
  <VTabs
    v-model="currentTab"
    grow
    stacked
  >
    <VTab value="tab-1">
      <VIcon
        icon="ri-phone-line"
        class="mb-2"
      />
      <span>Recent</span>
    </VTab>

    <VTab value="tab-2">
      <VIcon
        icon="ri-heart-line"
        class="mb-2"
      />
      <span>Favorites</span>
    </VTab>

    <VTab value="tab-3">
      <VIcon
        icon="ri-account-box-line"
        class="mb-2"
      />
      <span>Nearby</span>
    </VTab>
  </VTabs>

  <VWindow
    v-model="currentTab"
    class="mt-5"
  >
    <VWindowItem
      v-for="i in 3"
      :key="i"
      :value="\`tab-\${i}\`"
    >
      {{ tabItemText }}
    </VWindowItem>
  </VWindow>
</template>
`},Ae={ts:`<script setup lang="ts">
const currentTab = ref(0)
<\/script>

<template>
  <div class="d-flex">
    <VTabs
      v-model="currentTab"
      direction="vertical"
    >
      <VTab prepend-icon="ri-user-line">
        Option 1
      </VTab>
      <VTab prepend-icon="ri-lock-line">
        Option 2
      </VTab>
      <VTab prepend-icon="ri-rfid-line">
        Option 3
      </VTab>
    </VTabs>
    <VWindow
      v-model="currentTab"
      class="ms-3"
    >
      <VWindowItem>
        <p>
          Sed aliquam ultrices mauris. Donec posuere vulputate arcu. Morbi ac felis. Etiam feugiat lorem non metus. Sed a libero.
        </p>
        <p class="mb-0">
          Phasellus dolor. Fusce neque. Fusce fermentum odio nec arcu. Pellentesque libero tortor, tincidunt et, tincidunt eget, semper nec, quam. Phasellus blandit leo ut odio.
        </p>
      </VWindowItem>
      <VWindowItem>
        <p>
          Morbi nec metus. Suspendisse faucibus, nunc et pellentesque egestas, lacus ante convallis tellus, vitae iaculis lacus elit id tortor. Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo. Nunc sed turpis.
        </p>
        <p class="mb-0">
          Donec venenatis vulputate lorem. Aenean viverra rhoncus pede. In dui magna, posuere eget, vestibulum et, tempor auctor, justo. Fusce commodo aliquam arcu. Suspendisse enim turpis, dictum sed, iaculis a, condimentum nec, nisi.
        </p>
      </VWindowItem>
      <VWindowItem>
        <p>
          Fusce a quam. Phasellus nec sem in justo pellentesque facilisis. Nam eget dui. Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. In dui magna, posuere eget, vestibulum et, tempor auctor, justo.
        </p>
        <p class="mb-0">
          Cras sagittis. Phasellus nec sem in justo pellentesque facilisis. Proin sapien ipsum, porta a, auctor quis, euismod ut, mi. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nam at tortor in tellus interdum sagittis.
        </p>
      </VWindowItem>
    </VWindow>
  </div>
</template>
`,js:`<script setup>
const currentTab = ref(0)
<\/script>

<template>
  <div class="d-flex">
    <VTabs
      v-model="currentTab"
      direction="vertical"
    >
      <VTab prepend-icon="ri-user-line">
        Option 1
      </VTab>
      <VTab prepend-icon="ri-lock-line">
        Option 2
      </VTab>
      <VTab prepend-icon="ri-rfid-line">
        Option 3
      </VTab>
    </VTabs>
    <VWindow
      v-model="currentTab"
      class="ms-3"
    >
      <VWindowItem>
        <p>
          Sed aliquam ultrices mauris. Donec posuere vulputate arcu. Morbi ac felis. Etiam feugiat lorem non metus. Sed a libero.
        </p>
        <p class="mb-0">
          Phasellus dolor. Fusce neque. Fusce fermentum odio nec arcu. Pellentesque libero tortor, tincidunt et, tincidunt eget, semper nec, quam. Phasellus blandit leo ut odio.
        </p>
      </VWindowItem>
      <VWindowItem>
        <p>
          Morbi nec metus. Suspendisse faucibus, nunc et pellentesque egestas, lacus ante convallis tellus, vitae iaculis lacus elit id tortor. Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo. Nunc sed turpis.
        </p>
        <p class="mb-0">
          Donec venenatis vulputate lorem. Aenean viverra rhoncus pede. In dui magna, posuere eget, vestibulum et, tempor auctor, justo. Fusce commodo aliquam arcu. Suspendisse enim turpis, dictum sed, iaculis a, condimentum nec, nisi.
        </p>
      </VWindowItem>
      <VWindowItem>
        <p>
          Fusce a quam. Phasellus nec sem in justo pellentesque facilisis. Nam eget dui. Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. In dui magna, posuere eget, vestibulum et, tempor auctor, justo.
        </p>
        <p class="mb-0">
          Cras sagittis. Phasellus nec sem in justo pellentesque facilisis. Proin sapien ipsum, porta a, auctor quis, euismod ut, mi. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nam at tortor in tellus interdum sagittis.
        </p>
      </VWindowItem>
    </VWindow>
  </div>
</template>
`},ze={ts:`<script setup lang="ts">
const currentTab = ref('window-1')
<\/script>

<template>
  <div class="d-flex gap-6">
    <div>
      <VTabs
        v-model="currentTab"
        direction="vertical"
        class="v-tabs-pill"
      >
        <VTab>
          <VIcon
            start
            icon="ri-user-line"
          />
          Option 1
        </VTab>

        <VTab>
          <VIcon
            start
            icon="ri-lock-line"
          />
          Option 2
        </VTab>

        <VTab>
          <VIcon
            start
            icon="ri-rfid-line"
          />
          Option 3
        </VTab>
      </VTabs>
    </div>

    <VWindow v-model="currentTab">
      <VWindowItem value="window-1">
        <p>
          Sed aliquam ultrices mauris. Donec posuere vulputate arcu. Morbi ac felis. Etiam feugiat lorem non metus. Sed a libero.
        </p>

        <p class="mb-0">
          Phasellus dolor. Fusce neque. Fusce fermentum odio nec arcu. Pellentesque libero tortor, tincidunt et.
        </p>
      </VWindowItem>

      <VWindowItem value="window-2">
        <p class="mb-0">
          Morbi nec metus. Suspendisse faucibus, nunc et pellentesque egestas, lacus ante convallis tellus, vitae iaculis lacus elit id tortor. Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo. Nunc sed turpis.
        </p>
      </VWindowItem>

      <VWindowItem value="window-3">
        <p class="mb-0">
          Fusce a quam. Phasellus nec sem in justo pellentesque facilisis. Nam eget dui. Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. In dui magna, posuere eget, vestibulum et, tempor auctor, justo.
        </p>
      </VWindowItem>
    </VWindow>
  </div>
</template>
`,js:`<script setup>
const currentTab = ref('window-1')
<\/script>

<template>
  <div class="d-flex gap-6">
    <div>
      <VTabs
        v-model="currentTab"
        direction="vertical"
        class="v-tabs-pill"
      >
        <VTab>
          <VIcon
            start
            icon="ri-user-line"
          />
          Option 1
        </VTab>

        <VTab>
          <VIcon
            start
            icon="ri-lock-line"
          />
          Option 2
        </VTab>

        <VTab>
          <VIcon
            start
            icon="ri-rfid-line"
          />
          Option 3
        </VTab>
      </VTabs>
    </div>

    <VWindow v-model="currentTab">
      <VWindowItem value="window-1">
        <p>
          Sed aliquam ultrices mauris. Donec posuere vulputate arcu. Morbi ac felis. Etiam feugiat lorem non metus. Sed a libero.
        </p>

        <p class="mb-0">
          Phasellus dolor. Fusce neque. Fusce fermentum odio nec arcu. Pellentesque libero tortor, tincidunt et.
        </p>
      </VWindowItem>

      <VWindowItem value="window-2">
        <p class="mb-0">
          Morbi nec metus. Suspendisse faucibus, nunc et pellentesque egestas, lacus ante convallis tellus, vitae iaculis lacus elit id tortor. Sed mollis, eros et ultrices tempus, mauris ipsum aliquam libero, non adipiscing dolor urna a orci. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo. Nunc sed turpis.
        </p>
      </VWindowItem>

      <VWindowItem value="window-3">
        <p class="mb-0">
          Fusce a quam. Phasellus nec sem in justo pellentesque facilisis. Nam eget dui. Proin viverra, ligula sit amet ultrices semper, ligula arcu tristique sapien, a accumsan nisi mauris ac eros. In dui magna, posuere eget, vestibulum et, tempor auctor, justo.
        </p>
      </VWindowItem>
    </VWindow>
  </div>
</template>
`},Oe=o("p",null,[s("The "),o("code",null,"v-tabs"),s(" component is used for hiding content behind a selectable item.")],-1),Ue=o("p",null,[s("Use our custom class "),o("code",null,".v-tabs-pill"),s(" along with "),o("code",null,"v-tabs"),s(" component to style pill tabs.")],-1),Me=o("p",null,[s("Using "),o("code",null,"stacked"),s(" prop you can have buttons that use both icons and text.")],-1),Ee=o("p",null,[s("The "),o("code",null,"vertical"),s(" prop allows for "),o("code",null,"v-tab"),s(" components to stack vertically.")],-1),He=o("p",null,"Use our custom class .v-tabs-pill along with v-tabs component to style pill tabs.",-1),Re=o("p",null,[s("Use "),o("code",null,"align-tabs"),s(" prop to change the tabs alignment.")],-1),Ge=o("p",null,"If the tab items overflow their container, pagination controls will appear on desktop.",-1),Le=o("p",null,[o("code",null,"prev-icon"),s(" and "),o("code",null,"next-icon"),s(" props can be used for applying custom pagination icons.")],-1),Je=o("p",null,[s("The "),o("code",null,"fixed-tabs"),s(" prop forces "),o("code",null,"v-tab"),s(" to take up all available space up to the maximum width (300px).")],-1),Ke=o("p",null,[s("The "),o("code",null,"grow"),s(" prop will make the tab items take up all available space with no limit.")],-1),Qe=o("p",null,[s("Tabs can be dynamically added and removed. This allows you to update to any number and the "),o("code",null,"v-tabs"),s(" component will react.")],-1),na={__name:"tabs",setup(V){return(t,c)=>{const i=qe,n=z,r=ye,C=ke,y=_e,v=re,P=ne,S=ae,D=Y,j=K,$=G,F=H,B=U;return u(),k(A,{class:"match-height"},{default:a(()=>[e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Basic",code:Ce},{default:a(()=>[Oe,e(i)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Basic Pill",code:Pe},{default:a(()=>[Ue,e(r)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Stacked",code:Ne},{default:a(()=>[Me,e(C)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Vertical",code:Ae},{default:a(()=>[Ee,e(y)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Vertical Pill",code:ze},{default:a(()=>[He,e(v)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Alignment",code:We},{default:a(()=>[Re,e(P)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Pagination",code:Fe},{default:a(()=>[Ge,e(S)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Custom Icons",code:Se},{default:a(()=>[Le,e(D)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12"},{default:a(()=>[e(n,{title:"Fixed",code:je},{default:a(()=>[Je,e(j)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12"},{default:a(()=>[e(n,{title:"Grow",code:$e},{default:a(()=>[Ke,e($)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Programmatic Navigation",code:Be},{default:a(()=>[e(F)]),_:1},8,["code"])]),_:1}),e(_,{cols:"12",md:"6"},{default:a(()=>[e(n,{title:"Dynamic",code:De},{default:a(()=>[Qe,e(B)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{na as default};
