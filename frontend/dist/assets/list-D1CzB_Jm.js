import{o as n,g as m,w as t,c as h,F as V,y as b,a as e,q as c,m as w,s as _,t as L,p as f,v as x,e as a,z as A,K as N,n as T,i as J,f as B,a9 as W,b as p,D as P,E as U,G,H,ex as D,r as K,ey as S,am as k,d as Q,x as E,_ as q,j as Y,h as g}from"./index-C8KGzm5p.js";import{V as C}from"./VListItemAction-rKXZ2jGw.js";import{V as j}from"./VCheckbox-D7pWxu3R.js";import{_ as X}from"./AppCardCode-5Au5Cbm8.js";import{V as o}from"./VCardText-DqmU0q3b.js";import"./VCheckboxBtn-CqzEBXym.js";import"./_commonjsHelpers-BosuxZz1.js";import"./VCard-CA4PIBWr.js";const Z={__name:"DemoListShaped",setup(v){const l=[{text:"Cupcake sesame snaps dessert marzipan.",icon:"ri-instagram-line"},{text:"Jelly beans jelly-o gummi bears chupa chups marshmallow.",icon:"ri-facebook-circle-line"},{text:"Bonbon macaroon gummies pie jelly",icon:"ri-twitter-line"}];return(r,d)=>(n(),m(f,null,{default:t(()=>[(n(),h(V,null,b(l,(s,i)=>e(c,{key:i,value:s.text,rounded:"shaped"},{prepend:t(()=>[e(w,{icon:s.icon},null,8,["icon"])]),default:t(()=>[e(_,{textContent:L(s.text)},null,8,["textContent"])]),_:2},1032,["value"])),64))]),_:1}))}},ee={__name:"DemoListProgressList",setup(v){const l=[{avatar:"ri-reactjs-line",title:"React is a JavaScript library for building user interfaces",language:"react",amount:90},{avatar:"ri-bootstrap-line",title:"Bootstrap is an open source toolkit",language:"bootstrap",amount:80},{avatar:"ri-vuejs-line",title:"Vue.js is the Progressive JavaScript Framework",language:"vue",amount:65},{avatar:"ri-angularjs-line",title:"Angular implements Functional Programming concepts",language:"angular",amount:75},{avatar:"ri-javascript-line",title:"JavaScript is the programming language of the Web",language:"javascript",amount:70}],r={react:"info",bootstrap:"primary",vue:"success",angular:"error",javascript:"warning"};return(d,s)=>(n(),m(f,{lines:"two",border:"",rounded:""},{default:t(()=>[(n(),h(V,null,b(l,(i,u)=>(n(),h(V,{key:i.language},[e(c,null,{prepend:t(()=>[e(x,{size:"36",rounded:"",variant:"tonal",icon:i.avatar,color:r[i.language]},null,8,["icon","color"])]),default:t(()=>[e(_,null,{default:t(()=>[a(L(i.title),1)]),_:2},1024),e(A,{class:"mt-2"},{default:t(()=>[e(N,{height:"6",rounded:"","model-value":i.amount,color:r[i.language]},null,8,["model-value","color"])]),_:2},1024)]),_:2},1024),u!==l.length-1?(n(),m(T,{key:0})):J("",!0)],64))),64))]),_:1}))}},te={class:"ms-4"},ae={class:"text-xs text-disabled"},ie={__name:"DemoListUserList",setup(v){const l=[{avatar:P,name:"Caroline Black",status:"Online",lastVisited:"13 minutes ago"},{avatar:U,name:"Alfred Copeland",status:"Away",lastVisited:"11 minutes ago"},{avatar:G,name:"Celia Schneider",status:"Offline",lastVisited:"9 minutes ago"},{avatar:H,name:"Max Rogan",status:"In Meeting",lastVisited:"28 minutes ago"}],r={Online:"success",Away:"warning",Offline:"secondary","In Meeting":"error"};return(d,s)=>(n(),m(f,{lines:"two",border:"",rounded:""},{default:t(()=>[(n(),h(V,null,b(l,(i,u)=>(n(),h(V,{key:i.name},[e(c,null,{prepend:t(()=>[e(x,{image:i.avatar},null,8,["image"])]),append:t(()=>[e(B,{size:"small"},{default:t(()=>[a(" Add ")]),_:1})]),default:t(()=>[e(_,null,{default:t(()=>[a(L(i.name),1)]),_:2},1024),e(A,{class:"mt-1"},{default:t(()=>[e(W,{dot:"",location:"start center","offset-x":"2",color:r[i.status],class:"me-3"},{default:t(()=>[p("span",te,L(i.status),1)]),_:2},1032,["color"]),p("span",ae,L(i.lastVisited),1)]),_:2},1024)]),_:2},1024),u!==l.length-1?(n(),m(T,{key:0})):J("",!0)],64))),64))]),_:1}))}},se=["innerHTML"],le={__name:"DemoListThreeLine",setup(v){const l=[{type:"subheader",title:"Today"},{prependAvatar:P,title:"Brunch this weekend?",subtitle:`<span class="text-primary">Ali Connors</span> &mdash; I'll be in your neighborhood doing errands this weekend. Do you want to hang out?`},{type:"divider",inset:!0},{prependAvatar:U,title:"Summer BBQ",subtitle:`<span class="text-primary">to Alex, Scott, Jennifer</span> &mdash; Wish I could come, but I'm out of town this weekend.`},{type:"divider",inset:!0},{prependAvatar:G,title:"Oui oui",subtitle:'<span class="text-primary">Sandra Adams</span> &mdash; Do you have Paris recommendations? Have you ever been?'},{type:"divider",inset:!0},{prependAvatar:H,title:"Birthday gift",subtitle:'<span class="text-primary">Trevor Hansen</span> &mdash; Have any ideas about what we should get Heidi for her birthday?'}];return(r,d)=>(n(),m(f,{lines:"three",items:l,"item-props":""},{subtitle:t(({subtitle:s})=>[p("div",{innerHTML:s},null,8,se)]),_:1}))}},ne={__name:"DemoListTwoLinesAndSubheader",setup(v){const l=[{color:"blue",icon:"ri-clipboard-line",subtitle:"Jan 20, 2014",title:"Vacation itinerary"},{color:"amber",icon:"ri-hard-drive-2-line",subtitle:"Jan 10, 2014",title:"Kitchen remodel"}],r=[{subtitle:"Jan 9, 2014",title:"Photos"},{subtitle:"Jan 17, 2014",title:"Recipes"},{subtitle:"Jan 28, 2014",title:"Work"}];return(d,s)=>(n(),m(f,{lines:"two"},{default:t(()=>[e(D,{inset:""},{default:t(()=>[a(" Folders ")]),_:1}),(n(),h(V,null,b(r,i=>e(c,{key:i.title,title:i.title,subtitle:i.subtitle},{prepend:t(()=>[e(x,{color:"secondary",variant:"tonal"},{default:t(()=>[e(w,{size:26,icon:"ri-folder-line"})]),_:1})]),append:t(()=>[e(B,{variant:"text",color:"default",icon:"ri-information-line"})]),_:2},1032,["title","subtitle"])),64)),e(T,{inset:""}),e(D,{inset:""},{default:t(()=>[a(" Files ")]),_:1}),(n(),h(V,null,b(l,i=>e(c,{key:i.title,title:i.title,subtitle:i.subtitle},{prepend:t(()=>[e(x,{color:"secondary",variant:"tonal"},{default:t(()=>[e(w,{size:26,icon:i.icon},null,8,["icon"])]),_:2},1024)]),append:t(()=>[e(B,{variant:"text",color:"default",icon:"ri-information-line"})]),_:2},1032,["title","subtitle"])),64))]),_:1}))}},re={__name:"DemoListSubGroup",setup(v){const l=K(["Users","Admin"]),r=[["Management","ri-group-line"],["Settings","ri-settings-5-line"]],d=[["Create","ri-add-line"],["Read","ri-file-line"],["Update","ri-refresh-line"],["Delete","ri-delete-bin-line"]];return(s,i)=>(n(),m(f,{opened:Q(l),"onUpdate:opened":i[0]||(i[0]=u=>E(l)?l.value=u:null)},{default:t(()=>[e(c,{"prepend-icon":"ri-home-line",title:"Home",value:"Home"}),e(S,{value:"Users"},{activator:t(({props:u})=>[e(c,k(u,{"prepend-icon":"ri-user-line",title:"Users"}),null,16)]),default:t(()=>[e(S,{value:"Admin"},{activator:t(({props:u})=>[e(c,k(u,{title:"Admin"}),null,16)]),default:t(()=>[(n(),h(V,null,b(r,([u,y],I)=>e(c,{key:I,value:u,title:u,"prepend-icon":y},null,8,["value","title","prepend-icon"])),64))]),_:1}),e(S,{value:"Actions"},{activator:t(({props:u})=>[e(c,k(u,{title:"Actions"}),null,16)]),default:t(()=>[(n(),h(V,null,b(d,([u,y],I)=>e(c,{key:I,value:u,title:u,"prepend-icon":y},null,8,["value","title","prepend-icon"])),64))]),_:1})]),_:1})]),_:1},8,["opened"]))}},oe={};function ue(v,l){return n(),m(f,{lines:"three","select-strategy":"classic"},{default:t(()=>[e(D,null,{default:t(()=>[a("General")]),_:1}),e(c,{value:"notifications"},{prepend:t(({isActive:r})=>[e(C,null,{default:t(()=>[e(j,{"model-value":r,color:"primary"},null,8,["model-value"])]),_:2},1024)]),default:t(()=>[e(_,null,{default:t(()=>[a("Notifications")]),_:1}),e(A,null,{default:t(()=>[a("Notify me about updates to apps or games that I downloaded")]),_:1})]),_:1}),e(c,{value:"sound"},{prepend:t(({isActive:r})=>[e(C,null,{default:t(()=>[e(j,{"model-value":r,color:"primary"},null,8,["model-value"])]),_:2},1024)]),default:t(()=>[e(_,null,{default:t(()=>[a("Sound")]),_:1}),e(A,null,{default:t(()=>[a("Auto-update apps at any time. Data charges may apply")]),_:1})]),_:1}),e(c,{value:"widgets"},{prepend:t(({isActive:r})=>[e(C,null,{default:t(()=>[e(j,{"model-value":r,color:"primary"},null,8,["model-value"])]),_:2},1024)]),default:t(()=>[e(_,null,{default:t(()=>[a("Auto-add widgets")]),_:1}),e(A,null,{default:t(()=>[a("Automatically add home screen widgets when downloads complete")]),_:1})]),_:1})]),_:1})}const pe=q(oe,[["render",ue]]),ce={__name:"DemoListNav",setup(v){const l=[{title:"My Files",value:1,prependIcon:"ri-folder-line"},{title:"Shared with me",value:2,prependIcon:"ri-group-line"},{title:"Starred",value:3,prependIcon:"ri-star-line"},{title:"Recent",value:4,prependIcon:"ri-history-line"},{title:"Offline",value:5,prependIcon:"ri-checkbox-circle-line"},{title:"Uploads",value:6,prependIcon:"ri-upload-line"},{title:"Backups",value:7,prependIcon:"ri-upload-cloud-line"}];return(r,d)=>(n(),m(f,{nav:"",lines:!1},{default:t(()=>[(n(),h(V,null,b(l,s=>e(c,{key:s.value,value:s.value},{prepend:t(()=>[e(w,{icon:s.prependIcon},null,8,["icon"])]),default:t(()=>[e(_,null,{default:t(()=>[a(L(s.title),1)]),_:2},1024)]),_:2},1032,["value"])),64))]),_:1}))}},me={__name:"DemoListDensity",setup(v){const l=[{title:"halvah icing marshmallow",value:1},{title:"Cake caramels donut danish muffin biscuit",value:2},{title:"Chocolate cake pie lollipop",value:3},{title:"Apple pie toffee pudding gummi bears",value:4},{title:"Jujubes chupa chups cheesecake tart",value:5},{title:"Candy fruitcake bonbon sesame snaps dessert",value:6},{title:"Candy wafer tiramisu sugar plum sweet.",value:7},{title:"Toffee gingerbread muffin macaroon cotton candy bonbon lollipop.",value:8}];return(r,d)=>(n(),m(f,{density:"compact",items:l}))}},de={__name:"DemoListRounded",setup(v){const l=[{title:"Cupcake sesame snaps dessert marzipan.",value:1,props:{prependIcon:"ri-dribbble-line",rounded:"pill"}},{title:"Jelly beans jelly-o gummi bears chupa chups marshmallow.",value:2,props:{prependIcon:"ri-facebook-circle-line",rounded:"pill"}},{title:"Bonbon macaroon gummies pie jelly",value:3,props:{prependIcon:"ri-twitter-line",rounded:"pill"}},{title:"halvah icing marshmallow",value:4,props:{prependIcon:"ri-instagram-line",rounded:"pill"}}];return(r,d)=>(n(),m(f,{items:l}))}},ve={__name:"DemoListBasic",setup(v){const l=["Cras justo odio","Dapibus ac facilisis in","Morbi leo risus","Porta ac consectetur ac"];return(r,d)=>(n(),m(f,{items:l}))}},ge={ts:`<template>
  <VList
    lines="three"
    select-strategy="classic"
  >
    <VListSubheader>General</VListSubheader>

    <VListItem value="notifications">
      <template #prepend="{ isActive }">
        <VListItemAction>
          <VCheckbox
            :model-value="isActive"
            color="primary"
          />
        </VListItemAction>
      </template>

      <VListItemTitle>Notifications</VListItemTitle>
      <VListItemSubtitle>Notify me about updates to apps or games that I downloaded</VListItemSubtitle>
    </VListItem>

    <VListItem value="sound">
      <template #prepend="{ isActive }">
        <VListItemAction>
          <VCheckbox
            :model-value="isActive"
            color="primary"
          />
        </VListItemAction>
      </template>

      <VListItemTitle>Sound</VListItemTitle>
      <VListItemSubtitle>Auto-update apps at any time. Data charges may apply</VListItemSubtitle>
    </VListItem>

    <VListItem value="widgets">
      <template #prepend="{ isActive }">
        <VListItemAction>
          <VCheckbox
            :model-value="isActive"
            color="primary"
          />
        </VListItemAction>
      </template>

      <VListItemTitle>Auto-add widgets</VListItemTitle>
      <VListItemSubtitle>Automatically add home screen widgets when downloads complete</VListItemSubtitle>
    </VListItem>
  </VList>
</template>
`,js:`<template>
  <VList
    lines="three"
    select-strategy="classic"
  >
    <VListSubheader>General</VListSubheader>

    <VListItem value="notifications">
      <template #prepend="{ isActive }">
        <VListItemAction>
          <VCheckbox
            :model-value="isActive"
            color="primary"
          />
        </VListItemAction>
      </template>

      <VListItemTitle>Notifications</VListItemTitle>
      <VListItemSubtitle>Notify me about updates to apps or games that I downloaded</VListItemSubtitle>
    </VListItem>

    <VListItem value="sound">
      <template #prepend="{ isActive }">
        <VListItemAction>
          <VCheckbox
            :model-value="isActive"
            color="primary"
          />
        </VListItemAction>
      </template>

      <VListItemTitle>Sound</VListItemTitle>
      <VListItemSubtitle>Auto-update apps at any time. Data charges may apply</VListItemSubtitle>
    </VListItem>

    <VListItem value="widgets">
      <template #prepend="{ isActive }">
        <VListItemAction>
          <VCheckbox
            :model-value="isActive"
            color="primary"
          />
        </VListItemAction>
      </template>

      <VListItemTitle>Auto-add widgets</VListItemTitle>
      <VListItemSubtitle>Automatically add home screen widgets when downloads complete</VListItemSubtitle>
    </VListItem>
  </VList>
</template>
`},fe={ts:`<script setup lang="ts">
const items = ['Cras justo odio', 'Dapibus ac facilisis in', 'Morbi leo risus', 'Porta ac consectetur ac']
<\/script>

<template>
  <VList :items="items" />
</template>
`,js:`<script setup>
const items = [
  'Cras justo odio',
  'Dapibus ac facilisis in',
  'Morbi leo risus',
  'Porta ac consectetur ac',
]
<\/script>

<template>
  <VList :items="items" />
</template>
`},he={ts:`<script lang="ts" setup>
const items = [
  { title: 'halvah icing marshmallow', value: 1 },
  { title: 'Cake caramels donut danish muffin biscuit', value: 2 },
  { title: 'Chocolate cake pie lollipop', value: 3 },
  { title: 'Apple pie toffee pudding gummi bears', value: 4 },
  { title: 'Jujubes chupa chups cheesecake tart', value: 5 },
  { title: 'Candy fruitcake bonbon sesame snaps dessert', value: 6 },
  { title: 'Candy wafer tiramisu sugar plum sweet.', value: 7 },
  { title: 'Toffee gingerbread muffin macaroon cotton candy bonbon lollipop.', value: 8 },
]
<\/script>

<template>
  <VList
    density="compact"
    :items="items"
  />
</template>
`,js:`<script setup>
const items = [
  {
    title: 'halvah icing marshmallow',
    value: 1,
  },
  {
    title: 'Cake caramels donut danish muffin biscuit',
    value: 2,
  },
  {
    title: 'Chocolate cake pie lollipop',
    value: 3,
  },
  {
    title: 'Apple pie toffee pudding gummi bears',
    value: 4,
  },
  {
    title: 'Jujubes chupa chups cheesecake tart',
    value: 5,
  },
  {
    title: 'Candy fruitcake bonbon sesame snaps dessert',
    value: 6,
  },
  {
    title: 'Candy wafer tiramisu sugar plum sweet.',
    value: 7,
  },
  {
    title: 'Toffee gingerbread muffin macaroon cotton candy bonbon lollipop.',
    value: 8,
  },
]
<\/script>

<template>
  <VList
    density="compact"
    :items="items"
  />
</template>
`},Ve={ts:`<script lang="ts" setup>
const items = [
  { title: 'My Files', value: 1, prependIcon: 'ri-folder-line' },
  { title: 'Shared with me', value: 2, prependIcon: 'ri-group-line' },
  { title: 'Starred', value: 3, prependIcon: 'ri-star-line' },
  { title: 'Recent', value: 4, prependIcon: 'ri-history-line' },
  { title: 'Offline', value: 5, prependIcon: 'ri-checkbox-circle-line' },
  { title: 'Uploads', value: 6, prependIcon: 'ri-upload-line' },
  { title: 'Backups', value: 7, prependIcon: 'ri-upload-cloud-line' },
]
<\/script>

<template>
  <VList
    nav
    :lines="false"
  >
    <VListItem
      v-for="item in items"
      :key="item.value"
      :value="item.value"
    >
      <template #prepend>
        <VIcon :icon="item.prependIcon" />
      </template>

      <VListItemTitle>
        {{ item.title }}
      </VListItemTitle>
    </VListItem>
  </VList>
</template>
`,js:`<script setup>
const items = [
  {
    title: 'My Files',
    value: 1,
    prependIcon: 'ri-folder-line',
  },
  {
    title: 'Shared with me',
    value: 2,
    prependIcon: 'ri-group-line',
  },
  {
    title: 'Starred',
    value: 3,
    prependIcon: 'ri-star-line',
  },
  {
    title: 'Recent',
    value: 4,
    prependIcon: 'ri-history-line',
  },
  {
    title: 'Offline',
    value: 5,
    prependIcon: 'ri-checkbox-circle-line',
  },
  {
    title: 'Uploads',
    value: 6,
    prependIcon: 'ri-upload-line',
  },
  {
    title: 'Backups',
    value: 7,
    prependIcon: 'ri-upload-cloud-line',
  },
]
<\/script>

<template>
  <VList
    nav
    :lines="false"
  >
    <VListItem
      v-for="item in items"
      :key="item.value"
      :value="item.value"
    >
      <template #prepend>
        <VIcon :icon="item.prependIcon" />
      </template>

      <VListItemTitle>
        {{ item.title }}
      </VListItemTitle>
    </VListItem>
  </VList>
</template>
`},be={ts:`<script setup lang="ts">
interface Language {
  'react': string
  'bootstrap': string
  'vue': string
  'angular': string
  'javascript': string
}

interface Progress {
  avatar: string
  title: string
  language: keyof Language
  amount: number
}

const languageProgress: Progress[] = [
  {
    avatar: 'ri-reactjs-line',
    title: 'React is a JavaScript library for building user interfaces',
    language: 'react',
    amount: 90,
  },
  {
    avatar: 'ri-bootstrap-line',
    title: 'Bootstrap is an open source toolkit',
    language: 'bootstrap',
    amount: 80,
  },
  {
    avatar: 'ri-vuejs-line',
    title: 'Vue.js is the Progressive JavaScript Framework',
    language: 'vue',
    amount: 65,
  },
  {
    avatar: 'ri-angularjs-line',
    title: 'Angular implements Functional Programming concepts',
    language: 'angular',
    amount: 75,
  },
  {
    avatar: 'ri-javascript-line',
    title: 'JavaScript is the programming language of the Web',
    language: 'javascript',
    amount: 70,
  },
]

const resolveStatusColor: Language = {
  react: 'info',
  bootstrap: 'primary',
  vue: 'success',
  angular: 'error',
  javascript: 'warning',
}
<\/script>

<template>
  <VList
    lines="two"
    border
    rounded
  >
    <template
      v-for="(progress, index) of languageProgress"
      :key="progress.language"
    >
      <VListItem>
        <template #prepend>
          <VAvatar
            size="36"
            rounded
            variant="tonal"
            :icon="progress.avatar"
            :color="resolveStatusColor[progress.language]"
          />
        </template>

        <VListItemTitle>
          {{ progress.title }}
        </VListItemTitle>

        <VListItemSubtitle class="mt-2">
          <VProgressLinear
            height="6"
            rounded
            :model-value="progress.amount"
            :color="resolveStatusColor[progress.language]"
          />
        </VListItemSubtitle>
      </VListItem>

      <VDivider v-if="index !== languageProgress.length - 1" />
    </template>
  </VList>
</template>
`,js:`<script setup>
const languageProgress = [
  {
    avatar: 'ri-reactjs-line',
    title: 'React is a JavaScript library for building user interfaces',
    language: 'react',
    amount: 90,
  },
  {
    avatar: 'ri-bootstrap-line',
    title: 'Bootstrap is an open source toolkit',
    language: 'bootstrap',
    amount: 80,
  },
  {
    avatar: 'ri-vuejs-line',
    title: 'Vue.js is the Progressive JavaScript Framework',
    language: 'vue',
    amount: 65,
  },
  {
    avatar: 'ri-angularjs-line',
    title: 'Angular implements Functional Programming concepts',
    language: 'angular',
    amount: 75,
  },
  {
    avatar: 'ri-javascript-line',
    title: 'JavaScript is the programming language of the Web',
    language: 'javascript',
    amount: 70,
  },
]

const resolveStatusColor = {
  react: 'info',
  bootstrap: 'primary',
  vue: 'success',
  angular: 'error',
  javascript: 'warning',
}
<\/script>

<template>
  <VList
    lines="two"
    border
    rounded
  >
    <template
      v-for="(progress, index) of languageProgress"
      :key="progress.language"
    >
      <VListItem>
        <template #prepend>
          <VAvatar
            size="36"
            rounded
            variant="tonal"
            :icon="progress.avatar"
            :color="resolveStatusColor[progress.language]"
          />
        </template>

        <VListItemTitle>
          {{ progress.title }}
        </VListItemTitle>

        <VListItemSubtitle class="mt-2">
          <VProgressLinear
            height="6"
            rounded
            :model-value="progress.amount"
            :color="resolveStatusColor[progress.language]"
          />
        </VListItemSubtitle>
      </VListItem>

      <VDivider v-if="index !== languageProgress.length - 1" />
    </template>
  </VList>
</template>
`},_e={ts:`<script lang="ts" setup>
const items = [
  {
    title: 'Cupcake sesame snaps dessert marzipan.',
    value: 1,
    props: {
      prependIcon: 'ri-dribbble-line',
      rounded: 'pill',
    },
  },
  {
    title: 'Jelly beans jelly-o gummi bears chupa chups marshmallow.',
    value: 2,
    props: {
      prependIcon: 'ri-facebook-circle-line',
      rounded: 'pill',
    },
  },
  {
    title: 'Bonbon macaroon gummies pie jelly',
    value: 3,
    props: {
      prependIcon: 'ri-twitter-line',
      rounded: 'pill',
    },
  },
  {
    title: 'halvah icing marshmallow',
    value: 4,
    props: {
      prependIcon: 'ri-instagram-line',
      rounded: 'pill',
    },
  },
]
<\/script>

<template>
  <VList :items="items" />
</template>
`,js:`<script setup>
const items = [
  {
    title: 'Cupcake sesame snaps dessert marzipan.',
    value: 1,
    props: {
      prependIcon: 'ri-dribbble-line',
      rounded: 'pill',
    },
  },
  {
    title: 'Jelly beans jelly-o gummi bears chupa chups marshmallow.',
    value: 2,
    props: {
      prependIcon: 'ri-facebook-circle-line',
      rounded: 'pill',
    },
  },
  {
    title: 'Bonbon macaroon gummies pie jelly',
    value: 3,
    props: {
      prependIcon: 'ri-twitter-line',
      rounded: 'pill',
    },
  },
  {
    title: 'halvah icing marshmallow',
    value: 4,
    props: {
      prependIcon: 'ri-instagram-line',
      rounded: 'pill',
    },
  },
]
<\/script>

<template>
  <VList :items="items" />
</template>
`},Le={ts:`<script lang="ts" setup>
const items = [
  { text: 'Cupcake sesame snaps dessert marzipan.', icon: 'ri-instagram-line' },
  { text: 'Jelly beans jelly-o gummi bears chupa chups marshmallow.', icon: 'ri-facebook-circle-line' },
  { text: 'Bonbon macaroon gummies pie jelly', icon: 'ri-twitter-line' },
]
<\/script>

<template>
  <VList>
    <VListItem
      v-for="(item, i) in items"
      :key="i"
      :value="item.text"
      rounded="shaped"
    >
      <template #prepend>
        <VIcon :icon="item.icon" />
      </template>
      <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
      <VListItemTitle v-text="item.text" />
    </VListItem>
  </VList>
</template>
`,js:`<script setup>
const items = [
  {
    text: 'Cupcake sesame snaps dessert marzipan.',
    icon: 'ri-instagram-line',
  },
  {
    text: 'Jelly beans jelly-o gummi bears chupa chups marshmallow.',
    icon: 'ri-facebook-circle-line',
  },
  {
    text: 'Bonbon macaroon gummies pie jelly',
    icon: 'ri-twitter-line',
  },
]
<\/script>

<template>
  <VList>
    <VListItem
      v-for="(item, i) in items"
      :key="i"
      :value="item.text"
      rounded="shaped"
    >
      <template #prepend>
        <VIcon :icon="item.icon" />
      </template>
      <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
      <VListItemTitle v-text="item.text" />
    </VListItem>
  </VList>
</template>
`},ye={ts:`<script lang="ts" setup>
const open = ref(['Users', 'Admin'])

const admins = [
  ['Management', 'ri-group-line'],
  ['Settings', 'ri-settings-5-line'],
]

const cruds = [
  ['Create', 'ri-add-line'],
  ['Read', 'ri-file-line'],
  ['Update', 'ri-refresh-line'],
  ['Delete', 'ri-delete-bin-line'],
]
<\/script>

<template>
  <VList v-model:opened="open">
    <VListItem
      prepend-icon="ri-home-line"
      title="Home"
      value="Home"
    />

    <VListGroup value="Users">
      <template #activator="{ props }">
        <VListItem
          v-bind="props"
          prepend-icon="ri-user-line"
          title="Users"
        />
      </template>

      <VListGroup value="Admin">
        <template #activator="{ props }">
          <VListItem
            v-bind="props"
            title="Admin"
          />
        </template>

        <VListItem
          v-for="([title, icon], i) in admins"
          :key="i"
          :value="title"
          :title="title"
          :prepend-icon="icon"
        />
      </VListGroup>

      <VListGroup value="Actions">
        <template #activator="{ props }">
          <VListItem
            v-bind="props"
            title="Actions"
          />
        </template>

        <VListItem
          v-for="([title, icon], i) in cruds"
          :key="i"
          :value="title"
          :title="title"
          :prepend-icon="icon"
        />
      </VListGroup>
    </VListGroup>
  </VList>
</template>
`,js:`<script setup>
const open = ref([
  'Users',
  'Admin',
])

const admins = [
  [
    'Management',
    'ri-group-line',
  ],
  [
    'Settings',
    'ri-settings-5-line',
  ],
]

const cruds = [
  [
    'Create',
    'ri-add-line',
  ],
  [
    'Read',
    'ri-file-line',
  ],
  [
    'Update',
    'ri-refresh-line',
  ],
  [
    'Delete',
    'ri-delete-bin-line',
  ],
]
<\/script>

<template>
  <VList v-model:opened="open">
    <VListItem
      prepend-icon="ri-home-line"
      title="Home"
      value="Home"
    />

    <VListGroup value="Users">
      <template #activator="{ props }">
        <VListItem
          v-bind="props"
          prepend-icon="ri-user-line"
          title="Users"
        />
      </template>

      <VListGroup value="Admin">
        <template #activator="{ props }">
          <VListItem
            v-bind="props"
            title="Admin"
          />
        </template>

        <VListItem
          v-for="([title, icon], i) in admins"
          :key="i"
          :value="title"
          :title="title"
          :prepend-icon="icon"
        />
      </VListGroup>

      <VListGroup value="Actions">
        <template #activator="{ props }">
          <VListItem
            v-bind="props"
            title="Actions"
          />
        </template>

        <VListItem
          v-for="([title, icon], i) in cruds"
          :key="i"
          :value="title"
          :title="title"
          :prepend-icon="icon"
        />
      </VListGroup>
    </VListGroup>
  </VList>
</template>
`},Ie={ts:`<script lang="ts" setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'

const items = [
  { type: 'subheader', title: 'Today' },
  {
    prependAvatar: avatar1,
    title: 'Brunch this weekend?',
    subtitle: '<span class="text-primary">Ali Connors</span> &mdash; I'll be in your neighborhood doing errands this weekend. Do you want to hang out?',
  },
  { type: 'divider', inset: true },
  {
    prependAvatar: avatar2,
    title: 'Summer BBQ',
    subtitle: '<span class="text-primary">to Alex, Scott, Jennifer</span> &mdash; Wish I could come, but I'm out of town this weekend.',
  },
  { type: 'divider', inset: true },
  {
    prependAvatar: avatar3,
    title: 'Oui oui',
    subtitle: '<span class="text-primary">Sandra Adams</span> &mdash; Do you have Paris recommendations? Have you ever been?',
  },
  { type: 'divider', inset: true },
  {
    prependAvatar: avatar4,
    title: 'Birthday gift',
    subtitle: '<span class="text-primary">Trevor Hansen</span> &mdash; Have any ideas about what we should get Heidi for her birthday?',
  },
]
<\/script>

<template>
  <VList
    lines="three"
    :items="items"
    item-props
  >
    <template #subtitle="{ subtitle }">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="subtitle" />
    </template>
  </VList>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'

const items = [
  {
    type: 'subheader',
    title: 'Today',
  },
  {
    prependAvatar: avatar1,
    title: 'Brunch this weekend?',
    subtitle: '<span class="text-primary">Ali Connors</span> &mdash; I'll be in your neighborhood doing errands this weekend. Do you want to hang out?',
  },
  {
    type: 'divider',
    inset: true,
  },
  {
    prependAvatar: avatar2,
    title: 'Summer BBQ',
    subtitle: '<span class="text-primary">to Alex, Scott, Jennifer</span> &mdash; Wish I could come, but I'm out of town this weekend.',
  },
  {
    type: 'divider',
    inset: true,
  },
  {
    prependAvatar: avatar3,
    title: 'Oui oui',
    subtitle: '<span class="text-primary">Sandra Adams</span> &mdash; Do you have Paris recommendations? Have you ever been?',
  },
  {
    type: 'divider',
    inset: true,
  },
  {
    prependAvatar: avatar4,
    title: 'Birthday gift',
    subtitle: '<span class="text-primary">Trevor Hansen</span> &mdash; Have any ideas about what we should get Heidi for her birthday?',
  },
]
<\/script>

<template>
  <VList
    lines="three"
    :items="items"
    item-props
  >
    <template #subtitle="{ subtitle }">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="subtitle" />
    </template>
  </VList>
</template>
`},Ae={ts:`<script lang="ts" setup>
const files = [
  {
    color: 'blue',
    icon: 'ri-clipboard-line',
    subtitle: 'Jan 20, 2014',
    title: 'Vacation itinerary',
  },
  {
    color: 'amber',
    icon: 'ri-hard-drive-2-line',
    subtitle: 'Jan 10, 2014',
    title: 'Kitchen remodel',
  },
]

const folders = [
  {
    subtitle: 'Jan 9, 2014',
    title: 'Photos',
  },
  {
    subtitle: 'Jan 17, 2014',
    title: 'Recipes',
  },
  {
    subtitle: 'Jan 28, 2014',
    title: 'Work',
  },
]
<\/script>

<template>
  <VList lines="two">
    <VListSubheader inset>
      Folders
    </VListSubheader>

    <VListItem
      v-for="folder in folders"
      :key="folder.title"
      :title="folder.title"
      :subtitle="folder.subtitle"
    >
      <template #prepend>
        <VAvatar
          color="secondary"
          variant="tonal"
        >
          <VIcon
            :size="26"
            icon="ri-folder-line"
          />
        </VAvatar>
      </template>

      <template #append>
        <VBtn
          variant="text"
          color="default"
          icon="ri-information-line"
        />
      </template>
    </VListItem>

    <VDivider inset />

    <VListSubheader inset>
      Files
    </VListSubheader>

    <VListItem
      v-for="file in files"
      :key="file.title"
      :title="file.title"
      :subtitle="file.subtitle"
    >
      <template #prepend>
        <VAvatar
          color="secondary"
          variant="tonal"
        >
          <VIcon
            :size="26"
            :icon="file.icon"
          />
        </VAvatar>
      </template>

      <template #append>
        <VBtn
          variant="text"
          color="default"
          icon="ri-information-line"
        />
      </template>
    </VListItem>
  </VList>
</template>
`,js:`<script setup>
const files = [
  {
    color: 'blue',
    icon: 'ri-clipboard-line',
    subtitle: 'Jan 20, 2014',
    title: 'Vacation itinerary',
  },
  {
    color: 'amber',
    icon: 'ri-hard-drive-2-line',
    subtitle: 'Jan 10, 2014',
    title: 'Kitchen remodel',
  },
]

const folders = [
  {
    subtitle: 'Jan 9, 2014',
    title: 'Photos',
  },
  {
    subtitle: 'Jan 17, 2014',
    title: 'Recipes',
  },
  {
    subtitle: 'Jan 28, 2014',
    title: 'Work',
  },
]
<\/script>

<template>
  <VList lines="two">
    <VListSubheader inset>
      Folders
    </VListSubheader>

    <VListItem
      v-for="folder in folders"
      :key="folder.title"
      :title="folder.title"
      :subtitle="folder.subtitle"
    >
      <template #prepend>
        <VAvatar
          color="secondary"
          variant="tonal"
        >
          <VIcon
            :size="26"
            icon="ri-folder-line"
          />
        </VAvatar>
      </template>

      <template #append>
        <VBtn
          variant="text"
          color="default"
          icon="ri-information-line"
        />
      </template>
    </VListItem>

    <VDivider inset />

    <VListSubheader inset>
      Files
    </VListSubheader>

    <VListItem
      v-for="file in files"
      :key="file.title"
      :title="file.title"
      :subtitle="file.subtitle"
    >
      <template #prepend>
        <VAvatar
          color="secondary"
          variant="tonal"
        >
          <VIcon
            :size="26"
            :icon="file.icon"
          />
        </VAvatar>
      </template>

      <template #append>
        <VBtn
          variant="text"
          color="default"
          icon="ri-information-line"
        />
      </template>
    </VListItem>
  </VList>
</template>
`},we={ts:`<script setup lang="ts">
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'

interface Status {
  'Online': string
  'Away': string
  'Offline': string
  'In Meeting': string
}

interface Users {
  avatar: string
  name: string
  status: keyof Status
  lastVisited: string
}

const users: Users[] = [
  {
    avatar: avatar1,
    name: 'Caroline Black',
    status: 'Online',
    lastVisited: '13 minutes ago',
  },
  {
    avatar: avatar2,
    name: 'Alfred Copeland',
    status: 'Away',
    lastVisited: '11 minutes ago',
  },
  {
    avatar: avatar3,
    name: 'Celia Schneider',
    status: 'Offline',
    lastVisited: '9 minutes ago',
  },
  {
    avatar: avatar4,
    name: 'Max Rogan',
    status: 'In Meeting',
    lastVisited: '28 minutes ago',
  },
]

const resolveStatusColor: Status = {
  'Online': 'success',
  'Away': 'warning',
  'Offline': 'secondary',
  'In Meeting': 'error',
}
<\/script>

<template>
  <VList
    lines="two"
    border
    rounded
  >
    <template
      v-for="(user, index) of users"
      :key="user.name"
    >
      <VListItem>
        <template #prepend>
          <VAvatar :image="user.avatar" />
        </template>
        <VListItemTitle>
          {{ user.name }}
        </VListItemTitle>
        <VListItemSubtitle class="mt-1">
          <VBadge
            dot
            location="start center"
            offset-x="2"
            :color="resolveStatusColor[user.status]"
            class="me-3"
          >
            <span class="ms-4">{{ user.status }}</span>
          </VBadge>

          <span class="text-xs text-disabled">{{ user.lastVisited }}</span>
        </VListItemSubtitle>

        <template #append>
          <VBtn size="small">
            Add
          </VBtn>
        </template>
      </VListItem>
      <VDivider v-if="index !== users.length - 1" />
    </template>
  </VList>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'

const users = [
  {
    avatar: avatar1,
    name: 'Caroline Black',
    status: 'Online',
    lastVisited: '13 minutes ago',
  },
  {
    avatar: avatar2,
    name: 'Alfred Copeland',
    status: 'Away',
    lastVisited: '11 minutes ago',
  },
  {
    avatar: avatar3,
    name: 'Celia Schneider',
    status: 'Offline',
    lastVisited: '9 minutes ago',
  },
  {
    avatar: avatar4,
    name: 'Max Rogan',
    status: 'In Meeting',
    lastVisited: '28 minutes ago',
  },
]

const resolveStatusColor = {
  'Online': 'success',
  'Away': 'warning',
  'Offline': 'secondary',
  'In Meeting': 'error',
}
<\/script>

<template>
  <VList
    lines="two"
    border
    rounded
  >
    <template
      v-for="(user, index) of users"
      :key="user.name"
    >
      <VListItem>
        <template #prepend>
          <VAvatar :image="user.avatar" />
        </template>
        <VListItemTitle>
          {{ user.name }}
        </VListItemTitle>
        <VListItemSubtitle class="mt-1">
          <VBadge
            dot
            location="start center"
            offset-x="2"
            :color="resolveStatusColor[user.status]"
            class="me-3"
          >
            <span class="ms-4">{{ user.status }}</span>
          </VBadge>

          <span class="text-xs text-disabled">{{ user.lastVisited }}</span>
        </VListItemSubtitle>

        <template #append>
          <VBtn size="small">
            Add
          </VBtn>
        </template>
      </VListItem>
      <VDivider v-if="index !== users.length - 1" />
    </template>
  </VList>
</template>
`},xe=p("code",null,"v-list",-1),Se=p("code",null,"v-list-item",-1),ke=p("code",null,"rounded",-1),Ce=p("code",null,"density",-1),je=p("code",null,"default",-1),Be=p("code",null,"comfortable",-1),De=p("code",null,"compact",-1),Te=p("code",null,"nav",-1),Je=p("code",null,"v-list-item",-1),Pe=p("code",null,"three-line",-1),Ue=p("code",null,"v-list-group",-1),Ge=p("code",null,"v-list-group",-1),He=p("code",null,"v-list-item",-1),Ke={__name:"list",setup(v){return(l,r)=>{const d=ve,s=X,i=de,u=me,y=ce,I=pe,M=re,z=ne,O=le,R=ie,F=ee,$=Z;return n(),m(Y,{class:"match-height"},{default:t(()=>[e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Basic","no-padding":"",code:fe},{default:t(()=>[e(o,null,{default:t(()=>[xe,a(" component can contain an avatar, content, actions and much more.")]),_:1}),e(o,null,{default:t(()=>[e(d)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Rounded","no-padding":"",code:_e},{default:t(()=>[e(o,null,{default:t(()=>[a("You can make "),Se,a(" rounded using "),ke,a(" prop.")]),_:1}),e(o,null,{default:t(()=>[e(i)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Density",code:he,"no-padding":""},{default:t(()=>[e(o,null,{default:t(()=>[a("Use "),Ce,a(" prop to adjusts the spacing within the component. Available options are: "),je,a(", "),Be,a(", and "),De,a(".")]),_:1}),e(o,null,{default:t(()=>[e(u)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Nav","no-padding":"",code:Ve},{default:t(()=>[e(o,null,{default:t(()=>[a("Lists can receive an alternative "),Te,a(" styling that reduces the width "),Je,a(" takes up as well as adding a border radius.")]),_:1}),e(o,null,{default:t(()=>[e(y)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Action and item group","no-padding":"",code:ge},{default:t(()=>[e(o,null,{default:t(()=>[a("A "),Pe,a(" list with actions. Utilizing "),Ue,a(", easily connect actions to your tiles.")]),_:1}),e(o,null,{default:t(()=>[e(I)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Sub Group","no-padding":"",code:ye},{default:t(()=>[e(o,null,{default:t(()=>[a(" Using the "),Ge,a(" component you can create up to 2 levels in depth using the sub-group prop. ")]),_:1}),e(o,null,{default:t(()=>[e(M)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Two lines and subheader","no-padding":"",code:Ae},{default:t(()=>[e(o,null,{default:t(()=>[a("Lists can contain subheaders, dividers, and can contain 1 or more lines. The subtitle will overflow with ellipsis if it extends past one line.")]),_:1}),e(o,null,{default:t(()=>[e(z)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Three Line","no-padding":"",code:Ie},{default:t(()=>[e(o,null,{default:t(()=>[a("For three line lists, the subtitle will clamp vertically at 2 lines and then ellipsis. This feature uses line-clamp and is not supported in all browsers.")]),_:1}),e(o,null,{default:t(()=>[e(O)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"User List","no-padding":"",code:we},{default:t(()=>[e(o,null,{default:t(()=>[e(R)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Progress List","no-padding":"",code:be},{default:t(()=>[e(o,null,{default:t(()=>[e(F)]),_:1})]),_:1},8,["code"])]),_:1}),e(g,{cols:"12",md:"6"},{default:t(()=>[e(s,{title:"Shaped","no-padding":"",code:Le},{default:t(()=>[e(o,null,{default:t(()=>[a(" Shaped lists have rounded borders on one side of the "),He,a(". ")]),_:1}),e(o,null,{default:t(()=>[e($)]),_:1})]),_:1},8,["code"])]),_:1})]),_:1})}}};export{Ke as default};
