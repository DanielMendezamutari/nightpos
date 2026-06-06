import{u as oe}from"./useApi-BiIcnrZj.js";import{k as ne,r as w,J as se,o as u,c as A,a as e,w as a,j as q,h as x,d as o,x as J,b as s,V as T,t as l,F as re,y as ce,g as y,v as _,m as O,e as b,i as me,l as P,L as M,E as Y,aO as R,a6 as f,H as S,a7 as U,D as j,a5 as H,G as K,R as ie,f as $,am as de}from"./index-BaS1jTzJ.js";import{V as L}from"./VTextField-xk-ByVof.js";import{V as X}from"./VCardText-CnTiWaTJ.js";import{V as C}from"./VDataTable-BmK944c5.js";import{a as z}from"./formatters-COAB7S_x.js";import{V as ue}from"./VPagination-3W6fHhl2.js";import{V as Z,c as ee,b as ae}from"./VCard-CB7J203P.js";import{V as pe}from"./VContainer-BbCrBSTs.js";import{V as ve}from"./VSelect-Yec7nLFm.js";import{V as W}from"./VSpacer-BI5FvnR7.js";import{V as te}from"./VDialog-DSTCdxip.js";import{_ as ge}from"./AppCardCode-zDtyZoIC.js";/* empty css                   */import"./VCounter-41KmRdqH.js";import"./VField-BL8yai1I.js";import"./VCheckboxBtn-SHwSUMyG.js";import"./VTable-KQ9OYlSP.js";import"./filter-DyoGXi55.js";import"./helpers-cDhjtOJI.js";import"./_commonjsHelpers-BosuxZz1.js";const ye={class:"d-flex align-center"},fe={class:"d-flex flex-column ms-3"},xe={class:"d-block font-weight-medium text-truncate text-high-emphasis"},he={class:"text-xs"},De={class:"d-flex align-center"},be={class:"ms-1 text-no-wrap"},Ve={class:"d-flex align-center"},ke={key:1,class:"text-sm"},Ae={class:"text-no-wrap font-weight-medium text-high-emphasis ms-2"},Ie={class:"d-flex flex-column"},Ne={class:"d-flex align-center text-high-emphasis"},Se={key:0},Ce={class:"text-xs text-no-wrap"},we={__name:"DemoDataTableKitchenSink",async setup(I){let m,n;const{data:r}=([m,n]=ne(()=>oe("pages/datatable")),m=await m,n(),m),i=w(""),t=[{title:"PRODUCT",key:"product.name"},{title:"DATE",key:"date"},{title:"CATEGORY",key:"product.category"},{title:"BUYERS",key:"buyer.name"},{title:"PAYMENT",key:"payment",sortable:!1},{title:"STATUS",key:"status",sortable:!1},{title:"DELETE",key:"delete",sortable:!1}],p=D=>{if(!r.value)return;const k=r.value.findIndex(N=>N.product.id===D);r.value.splice(k,1)},v=[{name:"Mouse",icon:"ri-mouse-fill",color:"warning"},{name:"Glass",icon:"ri-glasses-line",color:"primary"},{name:"Smart Watch",icon:"ri-time-line",color:"success"},{name:"Bag",icon:"ri-shopping-bag-line",color:"info"},{name:"Storage Device",icon:"ri-tape-line",color:"warning"},{name:"Bluetooth",icon:"ri-bluetooth-line",color:"error"},{name:"Gaming",icon:"ri-gamepad-line",color:"warning"},{name:"Home",icon:"ri-home-line",color:"error"},{name:"VR",icon:"ri-goggles-line",color:"primary"},{name:"Shoes",icon:"ri-omega",color:"success"},{name:"Electronics",icon:"ri-flashlight-fill",color:"info"},{name:"Projector",icon:"ri-projector-line",color:"warning"},{name:"iPod",icon:"ri-music-line",color:"error"},{name:"Keyboard",icon:"ri-keyboard-box-line",color:"primary"},{name:"Smart Phone",icon:"ri-smartphone-line",color:"success"},{name:"Smart TV",icon:"ri-tv-line",color:"info"},{name:"Google Home",icon:"ri-google-line",color:"warning"},{name:"Mac",icon:"ri-apple-line",color:"error"},{name:"Headphone",icon:"ri-headphone-line",color:"primary"},{name:"iMac",icon:"ri-computer-line",color:"success"},{name:"iPhone",icon:"ri-apple-line",color:"warning"}],g=D=>{if(D==="Confirmed")return"primary";if(D==="Completed")return"success";if(D==="Cancelled")return"error"},E=D=>{const k=v.findIndex(N=>N.name===D);return k!==-1?[{icon:v[k].icon,color:v[k].color}]:[{icon:"ri-question-line",color:"primary"}]};return(D,k)=>{const N=se("IconBtn");return u(),A("div",null,[e(X,null,{default:a(()=>[e(q,null,{default:a(()=>[e(x,{cols:"12","offset-md":"8",md:"4"},{default:a(()=>[e(L,{modelValue:o(i),"onUpdate:modelValue":k[0]||(k[0]=c=>J(i)?i.value=c:null),label:"Search",placeholder:"Search ...","append-inner-icon":"ri-search-line","single-line":"","hide-details":"",dense:"",outlined:""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(C,{headers:t,items:o(r)||[],search:o(i),"items-per-page":5,class:"text-no-wrap"},{"item.product.name":a(({item:c})=>[s("div",ye,[s("div",null,[e(T,{src:c.product.image,height:"40",width:"40"},null,8,["src"])]),s("div",fe,[s("span",xe,l(c.product.name),1),s("span",he,l(c.product.brand),1)])])]),"item.product.category":a(({item:c})=>[s("div",De,[(u(!0),A(re,null,ce(E(c.product.category),(G,F)=>(u(),y(_,{key:F,size:"26",color:G.color,variant:"tonal"},{default:a(()=>[e(O,{size:"18",color:G.color,class:"rounded-0"},{default:a(()=>[b(l(G.icon),1)]),_:2},1032,["color"])]),_:2},1032,["color"]))),128)),s("span",be,l(c.product.category),1)])]),"item.buyer.name":a(({item:c})=>[s("div",Ve,[e(_,{size:"1.875rem",color:c.buyer.avatar?void 0:"primary",variant:c.buyer.avatar?void 0:"tonal"},{default:a(()=>[c.buyer.avatar?(u(),y(T,{key:0,src:c.buyer.avatar},null,8,["src"])):(u(),A("span",ke,l(c.buyer.name.slice(0,2).toUpperCase()),1))]),_:2},1032,["color","variant"]),s("span",Ae,l(c.buyer.name),1)])]),"item.payment":a(({item:c})=>[s("div",Ie,[s("div",Ne,[s("span",null,"$"+l(c.payment.paidAmount),1),c.payment.paidAmount!==c.payment.total?(u(),A("span",Se,"/"+l(c.payment.total),1)):me("",!0)]),s("span",Ce,l(c.payment.receivedPaymentStatus),1)])]),"item.status":a(({item:c})=>[e(P,{color:g(c.payment.status),class:M([`text-${g(c.payment.status)}`,"font-weight-medium"]),size:"small"},{default:a(()=>[b(l(c.payment.status),1)]),_:2},1032,["color","class"])]),"item.delete":a(({item:c})=>[e(N,{size:"small",onClick:G=>p(c.product.id)},{default:a(()=>[e(O,{icon:"ri-delete-bin-line"})]),_:2},1032,["onClick"])]),_:1},8,["items","search"])])}}},B=[{responsiveId:"",id:95,avatar:Y,fullName:"Edwina Ebsworth",post:"Human Resources Assistant",email:"eebsworth2m@sbwire.com",city:"Puzi",startDate:"09/27/2018",salary:19586.23,age:"27",experience:"2 Years",status:1},{responsiveId:"",id:1,avatar:R,fullName:"Korrie O'Crevy",post:"Nuclear Power Engineer",email:"kocrevy0@thetimes.co.uk",city:"Krasnosilka",startDate:"09/23/2016",salary:23896.35,age:"61",experience:"1 Year",status:2},{responsiveId:"",id:7,avatar:"",fullName:"Eileen Diehn",post:"Environmental Specialist",email:"ediehn6@163.com",city:"Lampuyang",startDate:"10/15/2017",salary:18991.67,age:"59",experience:"9 Years",status:3},{responsiveId:"",id:11,avatar:"",fullName:"De Falloon",post:"Sales Representative",email:"dfalloona@ifeng.com",city:"Colima",startDate:"06/12/2018",salary:19252.12,age:"30",experience:"0 Year",status:4},{responsiveId:"",id:3,avatar:f,fullName:"Stella Ganderton",post:"Operator",email:"sganderton2@tuttocitta.it",city:"Golcowa",startDate:"03/24/2018",salary:13076.28,age:"66",experience:"6 Years",status:5},{responsiveId:"",id:5,avatar:"",fullName:"Harmonia Nisius",post:"Senior Cost Accountant",email:"hnisius4@gnu.org",city:"Lucan",startDate:"08/25/2017",salary:10909.52,age:"33",experience:"3 Years",status:2},{responsiveId:"",id:6,avatar:"",fullName:"Genevra Honeywood",post:"Geologist",email:"ghoneywood5@narod.ru",city:"Maofan",startDate:"06/01/2017",salary:17803.8,age:"61",experience:"1 Year",status:1},{responsiveId:"",id:4,avatar:R,fullName:"Dorolice Crossman",post:"Cost Accountant",email:"dcrossman3@google.co.jp",city:"Paquera",startDate:"12/03/2017",salary:12336.17,age:"22",experience:"2 Years",status:2},{responsiveId:"",id:8,avatar:f,fullName:"Richardo Aldren",post:"Senior Sales Associate",email:"raldren7@mtv.com",city:"Skoghall",startDate:"11/05/2016",salary:19230.13,age:"55",experience:"5 Years",status:3},{responsiveId:"",id:9,avatar:Y,fullName:"Allyson Moakler",post:"Safety Technician",email:"amoakler8@shareasale.com",city:"Mogilany",startDate:"12/29/2018",salary:11677.32,age:"39",experience:"9 Years",status:5},{responsiveId:"",id:10,avatar:f,fullName:"Merline Penhalewick",post:"Junior Executive",email:"mpenhalewick9@php.net",city:"Kanuma",startDate:"04/19/2019",salary:15939.52,age:"23",experience:"3 Years",status:2},{responsiveId:"",id:12,avatar:"",fullName:"Cyrus Gornal",post:"Senior Sales Associate",email:"cgornalb@fda.gov",city:"Boro Utara",startDate:"12/09/2017",salary:16745.47,age:"22",experience:"2 Years",status:4},{responsiveId:"",id:13,avatar:"",fullName:"Tallou Balf",post:"Staff Accountant",email:"tbalfc@sina.com.cn",city:"Siliana",startDate:"01/21/2016",salary:15488.53,age:"36",experience:"6 Years",status:4},{responsiveId:"",id:14,avatar:"",fullName:"Othilia Extill",post:"Associate Professor",email:"oextilld@theatlantic.com",city:"Brzyska",startDate:"02/01/2016",salary:18442.34,age:"43",experience:"3 Years",status:2},{responsiveId:"",id:15,avatar:"",fullName:"Wilmar Bourton",post:"Administrative Assistant",email:"wbourtone@sakura.ne.jp",city:"Bích Động",startDate:"04/25/2018",salary:13304.45,age:"19",experience:"9 Years",status:5},{responsiveId:"",id:16,avatar:S,fullName:"Robinson Brazenor",post:"General Manager",email:"rbrazenorf@symantec.com",city:"Gendiwu",startDate:"12/23/2017",salary:11953.08,age:"66",experience:"6 Years",status:5},{responsiveId:"",id:17,avatar:"",fullName:"Nadia Bettenson",post:"Environmental Tech",email:"nbettensong@joomla.org",city:"Chabařovice",startDate:"07/11/2018",salary:20484.44,age:"64",experience:"4 Years",status:1},{responsiveId:"",id:18,avatar:"",fullName:"Titus Hayne",post:"Web Designer",email:"thayneh@kickstarter.com",city:"Yangon",startDate:"05/25/2019",salary:16871.48,age:"59",experience:"9 Years",status:1},{responsiveId:"",id:19,avatar:S,fullName:"Roxie Huck",post:"Administrative Assistant",email:"rhucki@ed.gov",city:"Polýkastro",startDate:"04/04/2019",salary:19653.56,age:"41",experience:"1 Year",status:4},{responsiveId:"",id:20,avatar:f,fullName:"Latashia Lewtey",post:"Actuary",email:"llewteyj@sun.com",city:"Hougong",startDate:"08/03/2017",salary:18303.87,age:"35",experience:"5 Years",status:1},{responsiveId:"",id:21,avatar:"",fullName:"Natalina Tyne",post:"Software Engineer",email:"ntynek@merriam-webster.com",city:"Yanguan",startDate:"03/16/2019",salary:15256.4,age:"30",experience:"0 Year",status:2},{responsiveId:"",id:22,avatar:"",fullName:"Faun Josefsen",post:"Analog Circuit Design manager",email:"fjosefsenl@samsung.com",city:"Wengyang",startDate:"07/08/2017",salary:11209.16,age:"40",experience:"0 Year",status:3},{responsiveId:"",id:23,avatar:f,fullName:"Rosmunda Steed",post:"Assistant Media Planner",email:"rsteedm@xing.com",city:"Manzanares",startDate:"12/23/2017",salary:13778.34,age:"21",experience:"1 Year",status:5},{responsiveId:"",id:24,avatar:"",fullName:"Scott Jiran",post:"Graphic Designer",email:"sjirann@simplemachines.org",city:"Pinglin",startDate:"05/26/2016",salary:23081.71,age:"23",experience:"3 Years",status:1},{responsiveId:"",id:25,avatar:"",fullName:"Carmita Medling",post:"Accountant",email:"cmedlingo@hp.com",city:"Bourges",startDate:"07/31/2019",salary:13602.24,age:"47",experience:"7 Years",status:3},{responsiveId:"",id:26,avatar:Y,fullName:"Morgen Benes",post:"Senior Sales Associate",email:"mbenesp@ted.com",city:"Cà Mau",startDate:"04/10/2016",salary:16969.63,age:"42",experience:"2 Years",status:4},{responsiveId:"",id:27,avatar:"",fullName:"Onfroi Doughton",post:"Civil Engineer",email:"odoughtonq@aboutads.info",city:"Utrecht (stad)",startDate:"09/29/2018",salary:23796.62,age:"28",experience:"8 Years",status:3},{responsiveId:"",id:28,avatar:"",fullName:"Kliment McGinney",post:"Chief Design Engineer",email:"kmcginneyr@paginegialle.it",city:"Xiaocheng",startDate:"07/09/2018",salary:24027.81,age:"28",experience:"8 Years",status:4},{responsiveId:"",id:29,avatar:"",fullName:"Devin Bridgland",post:"Tax Accountant",email:"dbridglands@odnoklassniki.ru",city:"Baoli",startDate:"07/17/2016",salary:13508.15,age:"48",experience:"8 Years",status:3},{responsiveId:"",id:30,avatar:U,fullName:"Gilbert McFade",post:"Biostatistician",email:"gmcfadet@irs.gov",city:"Deje",startDate:"08/28/2018",salary:21632.3,age:"20",experience:"0 Year",status:2},{responsiveId:"",id:31,avatar:"",fullName:"Teressa Bleakman",post:"Senior Editor",email:"tbleakmanu@phpbb.com",city:"Žebrák",startDate:"09/03/2016",salary:24875.41,age:"37",experience:"7 Years",status:5},{responsiveId:"",id:32,avatar:"",fullName:"Marcelia Alleburton",post:"Safety Technician",email:"malleburtonv@amazon.com",city:"Basail",startDate:"06/02/2016",salary:23888.98,age:"53",experience:"3 Years",status:2},{responsiveId:"",id:33,avatar:f,fullName:"Aili De Coursey",post:"Environmental Specialist",email:"adew@etsy.com",city:"Łazy",startDate:"09/30/2016",salary:14082.44,age:"27",experience:"7 Years",status:5},{responsiveId:"",id:34,avatar:U,fullName:"Charlton Chatres",post:"Analyst Programmer",email:"cchatresx@goo.gl",city:"Reguengos de Monsaraz",startDate:"04/07/2016",salary:21386.52,age:"22",experience:"2 Years",status:3},{responsiveId:"",id:35,avatar:j,fullName:"Nat Hugonnet",post:"Financial Advisor",email:"nhugonnety@wufoo.com",city:"Pimentel",startDate:"09/11/2019",salary:13835.97,age:"46",experience:"6 Years",status:4},{responsiveId:"",id:36,avatar:"",fullName:"Lorine Hearsum",post:"Payment Adjustment Coordinator",email:"lhearsumz@google.co.uk",city:"Shuiying",startDate:"03/05/2019",salary:22093.91,age:"47",experience:"7 Years",status:4},{responsiveId:"",id:37,avatar:H,fullName:"Sheila-kathryn Haborn",post:"Environmental Specialist",email:"shaborn10@about.com",city:"Lewolang",startDate:"11/10/2018",salary:24624.23,age:"51",experience:"1 Year",status:3},{responsiveId:"",id:38,avatar:K,fullName:"Alma Harvatt",post:"Administrative Assistant",email:"aharvatt11@addtoany.com",city:"Ulundi",startDate:"11/04/2016",salary:21782.82,age:"41",experience:"1 Year",status:1},{responsiveId:"",id:39,avatar:Y,fullName:"Beatrix Longland",post:"VP Quality Control",email:"blongland12@gizmodo.com",city:"Damu",startDate:"07/18/2016",salary:22794.6,age:"62",experience:"2 Years",status:2},{responsiveId:"",id:40,avatar:S,fullName:"Hammad Condell",post:"Project Manager",email:"hcondell13@tiny.cc",city:"Bulung'ur",startDate:"11/04/2018",salary:10872.83,age:"37",experience:"7 Years",status:4},{responsiveId:"",id:41,avatar:"",fullName:"Parker Bice",post:"Technical Writer",email:"pbice14@ameblo.jp",city:"Shanlian",startDate:"03/02/2016",salary:17471.92,age:"65",experience:"5 Years",status:5},{responsiveId:"",id:42,avatar:"",fullName:"Lowrance Orsi",post:"Biostatistician",email:"lorsi15@wp.com",city:"Dengteke",startDate:"12/10/2018",salary:24719.51,age:"64",experience:"4 Years",status:1},{responsiveId:"",id:43,avatar:R,fullName:"Ddene Chaplyn",post:"Environmental Tech",email:"dchaplyn16@nymag.com",city:"Lattes",startDate:"01/23/2019",salary:11958.33,age:"38",experience:"8 Years",status:2},{responsiveId:"",id:44,avatar:"",fullName:"Washington Bygraves",post:"Human Resources Manager",email:"wbygraves17@howstuffworks.com",city:"Zlaté Hory",startDate:"09/07/2016",salary:10552.43,age:"37",experience:"7 Years",status:1},{responsiveId:"",id:45,avatar:f,fullName:"Meghann Bodechon",post:"Operator",email:"mbodechon18@1und1.de",city:"Itō",startDate:"07/23/2018",salary:23024.28,age:"61",experience:"1 Year",status:4},{responsiveId:"",id:46,avatar:j,fullName:"Moshe De Ambrosis",post:"Recruiting Manager",email:"mde19@purevolume.com",city:"San Diego",startDate:"02/10/2018",salary:10409.9,age:"47",experience:"7 Years",status:5},{responsiveId:"",id:47,avatar:S,fullName:"Had Chatelot",post:"Cost Accountant",email:"hchatelot1a@usatoday.com",city:"Mercedes",startDate:"11/23/2016",salary:11446.3,age:"64",experience:"4 Years",status:4},{responsiveId:"",id:48,avatar:"",fullName:"Georgia McCrum",post:"Registered Nurse",email:"gmccrum1b@icio.us",city:"Nggalak",startDate:"04/19/2018",salary:14002.31,age:"63",experience:"3 Years",status:1},{responsiveId:"",id:49,avatar:H,fullName:"Krishnah Stilldale",post:"VP Accounting",email:"kstilldale1c@chronoengine.com",city:"Slavs'ke",startDate:"03/18/2017",salary:10704.29,age:"56",experience:"6 Years",status:1},{responsiveId:"",id:50,avatar:S,fullName:"Mario Umbert",post:"Research Assistant",email:"mumbert1d@digg.com",city:"Chorotis",startDate:"05/13/2019",salary:21813.54,age:"43",experience:"3 Years",status:1},{responsiveId:"",id:51,avatar:"",fullName:"Edvard Dixsee",post:"Graphic Designer",email:"edixsee1e@unblog.fr",city:"Rancharia",startDate:"04/23/2019",salary:18053.11,age:"46",experience:"6 Years",status:3},{responsiveId:"",id:52,avatar:f,fullName:"Tammie Davydoch",post:"VP Quality Control",email:"tdavydoch1f@examiner.com",city:"Mamedkala",startDate:"04/19/2016",salary:17617.08,age:"47",experience:"7 Years",status:3},{responsiveId:"",id:53,avatar:"",fullName:"Benito Rodolico",post:"Safety Technician",email:"brodolico1g@sciencedirect.com",city:"Wonosobo",startDate:"10/06/2018",salary:18866.55,age:"21",experience:"1 Year",status:5},{responsiveId:"",id:54,avatar:"",fullName:"Marco Pennings",post:"Compensation Analyst",email:"mpennings1h@bizjournals.com",city:"Umag",startDate:"06/15/2017",salary:13722.18,age:"30",experience:"0 Year",status:3},{responsiveId:"",id:55,avatar:"",fullName:"Tommie O'Corr",post:"Quality Engineer",email:"tocorr1i@nyu.edu",city:"Olhos de Água",startDate:"09/26/2018",salary:15228.8,age:"51",experience:"1 Year",status:1},{responsiveId:"",id:56,avatar:j,fullName:"Cybill Poyle",post:"Cost Accountant",email:"cpoyle1j@amazon.com",city:"Hamm",startDate:"01/03/2016",salary:13951.96,age:"29",experience:"9 Years",status:1},{responsiveId:"",id:57,avatar:U,fullName:"Norry Stoller",post:"Human Resources Manager",email:"nstoller1k@noaa.gov",city:"Ruukki",startDate:"02/04/2018",salary:15100,age:"27",experience:"7 Years",status:4},{responsiveId:"",id:58,avatar:"",fullName:"Wendi Somerlie",post:"Systems Administrator",email:"wsomerlie1l@accuweather.com",city:"Meicheng",startDate:"04/22/2016",salary:20023.52,age:"28",experience:"9 Years",status:5},{responsiveId:"",id:59,avatar:"",fullName:"Ferdie Georgeon",post:"Geologist",email:"fgeorgeon1m@nhs.uk",city:"Tanahbeureum",startDate:"04/08/2019",salary:12630.26,age:"28",experience:"1 Year",status:2},{responsiveId:"",id:60,avatar:"",fullName:"Jules Auten",post:"Desktop Support Technician",email:"jauten1n@foxnews.com",city:"Mojo",startDate:"08/13/2019",salary:13870.62,age:"48",experience:"5 Years",status:4},{responsiveId:"",id:61,avatar:K,fullName:"Nichole Dacres",post:"Mechanical Systems Engineer",email:"ndacres1o@apache.org",city:"Kimanuit",startDate:"11/06/2017",salary:18220.51,age:"20",experience:"0 Year",status:3},{responsiveId:"",id:62,avatar:j,fullName:"Holly Edgworth",post:"Junior Executive",email:"hedgworth1p@craigslist.org",city:"Pedreira",startDate:"08/05/2017",salary:13999.88,age:"37",experience:"0 Year",status:5},{responsiveId:"",id:63,avatar:f,fullName:"Henriette Croft",post:"Food Chemist",email:"hcroft1q@desdev.cn",city:"Taizhou",startDate:"09/12/2019",salary:11049.79,age:"53",experience:"1 Year",status:5},{responsiveId:"",id:64,avatar:"",fullName:"Annetta Glozman",post:"Staff Accountant",email:"aglozman1r@storify.com",city:"Pendawanbaru",startDate:"08/25/2017",salary:10745.32,age:"27",experience:"3 Years",status:5},{responsiveId:"",id:65,avatar:"",fullName:"Cletis Cervantes",post:"Health Coach",email:"ccervantes1s@de.vu",city:"Solnechnyy",startDate:"05/24/2018",salary:24769.08,age:"22",experience:"7 Years",status:1},{responsiveId:"",id:66,avatar:f,fullName:"Christos Kiley",post:"Geologist",email:"ckiley1t@buzzfeed.com",city:"El Bolsón",startDate:"02/27/2019",salary:16053.15,age:"46",experience:"2 Years",status:1},{responsiveId:"",id:67,avatar:f,fullName:"Silvain Siebert",post:"VP Sales",email:"ssiebert1u@domainmarket.com",city:"Cadiz",startDate:"09/23/2017",salary:23347.17,age:"47",experience:"8 Years",status:5},{responsiveId:"",id:68,avatar:"",fullName:"Sharla Ibberson",post:"Payment Adjustment Coordinator",email:"sibberson1v@virginia.edu",city:"Lamam",startDate:"11/01/2016",salary:15658.4,age:"51",experience:"8 Years",status:1},{responsiveId:"",id:69,avatar:f,fullName:"Ripley Rentcome",post:"Physical Therapy Assistant",email:"rrentcome1w@youtu.be",city:"Dashkawka",startDate:"07/15/2018",salary:15396.66,age:"41",experience:"8 Years",status:2},{responsiveId:"",id:70,avatar:"",fullName:"Chrisse Birrane",post:"Chemical Engineer",email:"cbirrane1x@google.com.br",city:"Las Toscas",startDate:"05/22/2016",salary:15823.4,age:"62",experience:"0 Year",status:5},{responsiveId:"",id:71,avatar:"",fullName:"Georges Tesyro",post:"Human Resources Manager",email:"gtesyro1y@last.fm",city:"Gabao",startDate:"01/27/2019",salary:19051.25,age:"37",experience:"7 Years",status:1},{responsiveId:"",id:72,avatar:"",fullName:"Bondon Hazard",post:"Geological Engineer",email:"bhazard1z@over-blog.com",city:"Llano de Piedra",startDate:"01/17/2019",salary:11632.84,age:"65",experience:"3 Years",status:4},{responsiveId:"",id:73,avatar:S,fullName:"Aliza MacElholm",post:"VP Sales",email:"amacelholm20@printfriendly.com",city:"Sosnovyy Bor",startDate:"11/17/2017",salary:16741.31,age:"64",experience:"7 Years",status:2},{responsiveId:"",id:74,avatar:Y,fullName:"Lucas Witherdon",post:"Senior Quality Engineer",email:"lwitherdon21@storify.com",city:"Staré Křečany",startDate:"09/26/2016",salary:19387.76,age:"38",experience:"2 Years",status:3},{responsiveId:"",id:75,avatar:"",fullName:"Pegeen Peasegod",post:"Web Designer",email:"ppeasegod22@slideshare.net",city:"Keda",startDate:"05/21/2016",salary:24014.04,age:"59",experience:"6 Years",status:3},{responsiveId:"",id:76,avatar:"",fullName:"Elyn Watkinson",post:"Structural Analysis Engineer",email:"ewatkinson23@blogspot.com",city:"Osan",startDate:"09/30/2016",salary:14493.51,age:"55",experience:"7 Years",status:1},{responsiveId:"",id:77,avatar:R,fullName:"Babb Skirving",post:"Analyst Programmer",email:"bskirving24@cbsnews.com",city:"Balky",startDate:"09/27/2016",salary:24733.28,age:"39",experience:"1 Year",status:4},{responsiveId:"",id:78,avatar:"",fullName:"Shelli Ondracek",post:"Financial Advisor",email:"sondracek25@plala.or.jp",city:"Aoluguya Ewenke Minzu",startDate:"03/28/2016",salary:21922.17,age:"23",experience:"1 Year",status:3},{responsiveId:"",id:79,avatar:f,fullName:"Stanislaw Melloy",post:"Sales Associate",email:"smelloy26@fastcompany.com",city:"Funafuti",startDate:"04/13/2017",salary:16944.42,age:"30",experience:"2 Years",status:2},{responsiveId:"",id:80,avatar:"",fullName:"Seamus Eisikovitsh",post:"Legal Assistant",email:"seisikovitsh27@usgs.gov",city:"Cangkringan",startDate:"05/28/2018",salary:21963.69,age:"22",experience:"7 Years",status:1},{responsiveId:"",id:81,avatar:Y,fullName:"Tammie Wattins",post:"Web Designer",email:"twattins28@statcounter.com",city:"Xilin",startDate:"08/07/2018",salary:16049.93,age:"36",experience:"5 Years",status:2},{responsiveId:"",id:82,avatar:H,fullName:"Aila Quailadis",post:"Technical Writer",email:"aquail29@prlog.org",city:"Shuangchahe",startDate:"02/11/2018",salary:24137.29,age:"43",experience:"4 Years",status:4},{responsiveId:"",id:83,avatar:"",fullName:"Myrvyn Gilogly",post:"Research Associate",email:"mgilogly2a@elpais.com",city:"Prince Rupert",startDate:"05/13/2018",salary:10089.96,age:"19",experience:"8 Years",status:4},{responsiveId:"",id:84,avatar:S,fullName:"Hanna Langthorne",post:"Analyst Programmer",email:"hlangthorne2b@stumbleupon.com",city:"Guaynabo",startDate:"11/11/2018",salary:14227.1,age:"21",experience:"7 Years",status:3},{responsiveId:"",id:85,avatar:"",fullName:"Ruby Gimblet",post:"Registered Nurse",email:"rgimblet2c@1688.com",city:"Nanyulinxi",startDate:"03/28/2016",salary:19562.59,age:"30",experience:"1 Year",status:2},{responsiveId:"",id:86,avatar:S,fullName:"Louis Paszak",post:"Programmer",email:"lpaszak2d@behance.net",city:"Chiscas",startDate:"04/25/2016",salary:17178.86,age:"51",experience:"7 Years",status:5},{responsiveId:"",id:87,avatar:"",fullName:"Glennie Riolfi",post:"Computer Systems Analyst",email:"griolfi2e@drupal.org",city:"Taung",startDate:"06/18/2018",salary:15089.83,age:"29",experience:"4 Years",status:3},{responsiveId:"",id:88,avatar:"",fullName:"Jemimah Morgan",post:"Staff Accountant",email:"jmorgan2f@nifty.com",city:"La Esperanza",startDate:"01/17/2016",salary:18330.72,age:"27",experience:"3 Years",status:1},{responsiveId:"",id:89,avatar:R,fullName:"Talya Brandon",post:"Food Chemist",email:"tbrandon2g@ucoz.com",city:"Zaječar",startDate:"10/08/2018",salary:16284.64,age:"28",experience:"6 Years",status:1},{responsiveId:"",id:90,avatar:U,fullName:"Renate Shay",post:"Recruiter",email:"rshay2h@tumblr.com",city:"Pueblo Viejo",startDate:"03/15/2017",salary:18523.75,age:"28",experience:"3 Years",status:1},{responsiveId:"",id:91,avatar:"",fullName:"Julianne Bartosik",post:"Senior Cost Accountant",email:"jbartosik2i@state.gov",city:"Botlhapatlou",startDate:"02/06/2017",salary:17607.66,age:"48",experience:"6 Years",status:3},{responsiveId:"",id:92,avatar:K,fullName:"Yvonne Emberton",post:"Recruiter",email:"yemberton2j@blog.com",city:"Nagcarlan",startDate:"02/13/2017",salary:17550.18,age:"20",experience:"1 Year",status:4},{responsiveId:"",id:93,avatar:H,fullName:"Danya Faichnie",post:"Social Worker",email:"dfaichnie2k@weather.com",city:"Taling",startDate:"07/29/2019",salary:18469.35,age:"37",experience:"3 Years",status:4},{responsiveId:"",id:94,avatar:"",fullName:"Ronica Hasted",post:"Software Consultant",email:"rhasted2l@hexun.com",city:"Gangkou",startDate:"07/04/2019",salary:24866.66,age:"53",experience:"7 Years",status:4},{responsiveId:"",id:96,avatar:"",fullName:"Alaric Beslier",post:"Tax Accountant",email:"abeslier2n@zimbio.com",city:"Ocucaje",startDate:"04/16/2017",salary:19366.53,age:"22",experience:"8 Years",status:4},{responsiveId:"",id:2,avatar:j,fullName:"Bailie Coulman",post:"VP Quality Control",email:"bcoulman1@yolasite.com",city:"Hinigaran",startDate:"05/20/2018",salary:13633.69,age:"63",experience:"3 Years",status:2},{responsiveId:"",id:97,avatar:"",fullName:"Reina Peckett",post:"Quality Control Specialist",email:"rpeckett2o@timesonline.co.uk",city:"Anyang",startDate:"05/20/2018",salary:16619.4,age:"46",experience:"8 Years",status:4},{responsiveId:"",id:98,avatar:f,fullName:"Olivette Gudgin",post:"Paralegal",email:"ogudgin2p@gizmodo.com",city:"Fujinomiya",startDate:"04/09/2019",salary:15211.6,age:"47",experience:"8 Years",status:2},{responsiveId:"",id:99,avatar:R,fullName:"Evangelina Carnock",post:"Cost Accountant",email:"ecarnock2q@washington.edu",city:"Doushaguan",startDate:"01/26/2017",salary:23704.82,age:"51",experience:"0 Year",status:4},{responsiveId:"",id:100,avatar:"",fullName:"Glyn Giacoppo",post:"Software Test Engineer",email:"ggiacoppo2r@apache.org",city:"Butha-Buthe",startDate:"04/15/2017",salary:24973.48,age:"41",experience:"7 Years",status:2}],Te={class:"d-flex align-center"},_e={key:1,class:"text-sm"},Ee={class:"d-flex flex-column ms-3"},Ye={class:"d-block font-weight-medium text-high-emphasis text-truncate"},Re={class:"d-flex flex-wrap justify-center justify-sm-space-between gap-y-2 mt-2"},Pe={__name:"DemoDataTableExternalPagination",setup(I){const m=w([]),n=w({page:1,itemsPerPage:5,sortBy:[""],sortDesc:[!1]}),r=[{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"}],i=t=>t===1?{color:"primary",text:"Current"}:t===2?{color:"success",text:"Professional"}:t===3?{color:"error",text:"Rejected"}:t===4?{color:"warning",text:"Resigned"}:{color:"info",text:"Applied"};return ie(()=>{m.value=JSON.parse(JSON.stringify(B))}),(t,p)=>(u(),y(C,{headers:r,items:o(m),"items-per-page":o(n).itemsPerPage,page:o(n).page,options:o(n),class:"text-no-wrap"},{"item.fullName":a(({item:v})=>[s("div",Te,[e(_,{size:"32",color:v.avatar?"":"primary",class:M(v.avatar?"":"v-avatar-light-bg primary--text"),variant:v.avatar?void 0:"tonal"},{default:a(()=>[v.avatar?(u(),y(T,{key:0,src:v.avatar},null,8,["src"])):(u(),A("span",_e,l(("avatarText"in t?t.avatarText:o(z))(v.fullName)),1))]),_:2},1032,["color","class","variant"]),s("div",Ee,[s("span",Ye,l(v.fullName),1),s("small",null,l(v.post),1)])])]),"item.status":a(({item:v})=>[e(P,{color:i(v.status).color,class:"font-weight-medium",size:"small"},{default:a(()=>[b(l(i(v.status).text),1)]),_:2},1032,["color"])]),bottom:a(()=>[e(X,{class:"pt-2"},{default:a(()=>[s("div",Re,[e(L,{modelValue:o(n).itemsPerPage,"onUpdate:modelValue":p[0]||(p[0]=v=>o(n).itemsPerPage=v),label:"Rows per page:",type:"number",min:"1",max:"15","hide-details":"",variant:"underlined",style:{"max-inline-size":"8rem","min-inline-size":"5rem"}},null,8,["modelValue"]),e(ue,{modelValue:o(n).page,"onUpdate:modelValue":p[1]||(p[1]=v=>o(n).page=v),"total-visible":t.$vuetify.display.smAndDown?2:3,length:Math.ceil(o(m).length/o(n).itemsPerPage)},null,8,["modelValue","total-visible","length"])])]),_:1})]),_:1},8,["items","items-per-page","page","options"]))}},Me={class:"d-flex align-center"},Be={key:1,class:"text-sm"},Le={class:"d-flex flex-column ms-3"},ze={class:"d-block font-weight-medium text-high-emphasis text-truncate"},Ge={class:"d-flex gap-1"},je=s("span",{class:"headline"},"Edit Item",-1),$e={__name:"DemoDataTableRowEditingViaDialog",setup(I){const m=w(!1),n=w(!1),r=w({responsiveId:"",id:-1,avatar:"",fullName:"",post:"",email:"",city:"",startDate:"",salary:-1,age:"",experience:"",status:-1}),i=w(r.value),t=w(-1),p=w([]),v=[{text:"Current",value:1},{text:"Professional",value:2},{text:"Rejected",value:3},{text:"Resigned",value:4},{text:"Applied",value:5}],g=[{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"},{title:"ACTIONS",key:"actions"}],E=V=>V===1?{color:"primary",text:"Current"}:V===2?{color:"success",text:"Professional"}:V===3?{color:"error",text:"Rejected"}:V===4?{color:"warning",text:"Resigned"}:{color:"info",text:"Applied"},D=V=>{t.value=p.value.indexOf(V),i.value={...V},m.value=!0},k=V=>{t.value=p.value.indexOf(V),i.value={...V},n.value=!0},N=()=>{m.value=!1,t.value=-1,i.value={...r.value}},c=()=>{n.value=!1,t.value=-1,i.value={...r.value}},G=()=>{t.value>-1?Object.assign(p.value[t.value],i.value):p.value.push(i.value),N()},F=()=>{p.value.splice(t.value,1),c()};return ie(()=>{p.value=JSON.parse(JSON.stringify(B))}),(V,h)=>{const Q=se("IconBtn");return u(),A(re,null,[e(C,{headers:g,items:o(p),"items-per-page":5,class:"text-no-wrap"},{"item.fullName":a(({item:d})=>[s("div",Me,[e(_,{size:"32",color:d.avatar?"":"primary",class:M(d.avatar?"":"v-avatar-light-bg primary--text"),variant:d.avatar?void 0:"tonal"},{default:a(()=>[d.avatar?(u(),y(T,{key:0,src:d.avatar},null,8,["src"])):(u(),A("span",Be,l(("avatarText"in V?V.avatarText:o(z))(d.fullName)),1))]),_:2},1032,["color","class","variant"]),s("div",Le,[s("span",ze,l(d.fullName),1),s("small",null,l(d.post),1)])])]),"item.status":a(({item:d})=>[e(P,{color:E(d.status).color,density:"comfortable"},{default:a(()=>[b(l(E(d.status).text),1)]),_:2},1032,["color"])]),"item.actions":a(({item:d})=>[s("div",Ge,[e(Q,{size:"small",onClick:le=>D(d)},{default:a(()=>[e(O,{icon:"ri-pencil-line"})]),_:2},1032,["onClick"]),e(Q,{size:"small",onClick:le=>k(d)},{default:a(()=>[e(O,{icon:"ri-delete-bin-line"})]),_:2},1032,["onClick"])])]),_:1},8,["items"]),e(te,{modelValue:o(m),"onUpdate:modelValue":h[6]||(h[6]=d=>J(m)?m.value=d:null),"max-width":"600px"},{default:a(()=>[e(Z,null,{default:a(()=>[e(ee,null,{default:a(()=>[je]),_:1}),e(X,null,{default:a(()=>[e(pe,null,{default:a(()=>[e(q,null,{default:a(()=>[e(x,{cols:"12",sm:"6",md:"4"},{default:a(()=>[e(L,{modelValue:o(i).fullName,"onUpdate:modelValue":h[0]||(h[0]=d=>o(i).fullName=d),label:"User name"},null,8,["modelValue"])]),_:1}),e(x,{cols:"12",sm:"6",md:"4"},{default:a(()=>[e(L,{modelValue:o(i).email,"onUpdate:modelValue":h[1]||(h[1]=d=>o(i).email=d),label:"Email"},null,8,["modelValue"])]),_:1}),e(x,{cols:"12",sm:"6",md:"4"},{default:a(()=>[e(L,{modelValue:o(i).salary,"onUpdate:modelValue":h[2]||(h[2]=d=>o(i).salary=d),label:"Salary",prefix:"$",type:"number"},null,8,["modelValue"])]),_:1}),e(x,{cols:"12",sm:"6",md:"4"},{default:a(()=>[e(L,{modelValue:o(i).age,"onUpdate:modelValue":h[3]||(h[3]=d=>o(i).age=d),label:"Age",type:"number"},null,8,["modelValue"])]),_:1}),e(x,{cols:"12",sm:"6",md:"4"},{default:a(()=>[e(L,{modelValue:o(i).startDate,"onUpdate:modelValue":h[4]||(h[4]=d=>o(i).startDate=d),label:"Date"},null,8,["modelValue"])]),_:1}),e(x,{cols:"12",sm:"6",md:"4"},{default:a(()=>[e(ve,{modelValue:o(i).status,"onUpdate:modelValue":h[5]||(h[5]=d=>o(i).status=d),items:v,"item-title":"text","item-value":"value",label:"Status",variant:"outlined"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1}),e(ae,null,{default:a(()=>[e(W),e($,{color:"error",variant:"outlined",onClick:N},{default:a(()=>[b(" Cancel ")]),_:1}),e($,{color:"success",variant:"elevated",onClick:G},{default:a(()=>[b(" Save ")]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"]),e(te,{modelValue:o(n),"onUpdate:modelValue":h[7]||(h[7]=d=>J(n)?n.value=d:null),"max-width":"500px"},{default:a(()=>[e(Z,null,{default:a(()=>[e(ee,null,{default:a(()=>[b(" Are you sure you want to delete this item? ")]),_:1}),e(ae,null,{default:a(()=>[e(W),e($,{color:"error",variant:"outlined",onClick:c},{default:a(()=>[b(" Cancel ")]),_:1}),e($,{color:"success",variant:"elevated",onClick:F},{default:a(()=>[b(" OK ")]),_:1}),e(W)]),_:1})]),_:1})]),_:1},8,["modelValue"])],64)}}},Oe={class:"d-flex align-center"},Ue={key:1,class:"text-sm"},He={class:"d-flex flex-column ms-3"},Fe={class:"d-block font-weight-medium text-high-emphasis text-truncate"},Ke={__name:"DemoDataTableGroupingRows",setup(I){const m=[{responsiveId:"",id:1,avatar:R,fullName:"Korrie O'Crevy",post:"Nuclear Power Engineer",email:"kocrevy0@thetimes.co.uk",city:"Krasnosilka",startDate:"09/23/2016",salary:23896.35,age:"61",experience:"1 Year",status:"Professional"},{responsiveId:"",id:2,avatar:j,fullName:"Bailie Coulman",post:"VP Quality Control",email:"bcoulman1@yolasite.com",city:"Hinigaran",startDate:"05/20/2018",salary:13633.69,age:"63",experience:"3 Years",status:"Professional"},{responsiveId:"",id:3,avatar:f,fullName:"Stella Ganderton",post:"Operator",email:"sganderton2@tuttocitta.it",city:"Golcowa",startDate:"03/24/2018",salary:13076.28,age:"66",experience:"6 Years",status:"Applied"},{responsiveId:"",id:4,avatar:R,fullName:"Dorolice Crossman",post:"Cost Accountant",email:"dcrossman3@google.co.jp",city:"Paquera",startDate:"12/03/2017",salary:12336.17,age:"22",experience:"2 Years",status:"Professional"},{responsiveId:"",id:6,avatar:"",fullName:"Genevra Honeywood",post:"Geologist",email:"ghoneywood5@narod.ru",city:"Maofan",startDate:"06/01/2017",salary:17803.8,age:"61",experience:"1 Year",status:"Current"},{responsiveId:"",id:7,avatar:"",fullName:"Eileen Diehn",post:"Environmental Specialist",email:"ediehn6@163.com",city:"Lampuyang",startDate:"10/15/2017",salary:18991.67,age:"59",experience:"9 Years",status:"Rejected"},{responsiveId:"",id:8,avatar:f,fullName:"Richardo Aldren",post:"Senior Sales Associate",email:"raldren7@mtv.com",city:"Skoghall",startDate:"11/05/2016",salary:19230.13,age:"55",experience:"5 Years",status:"Rejected"},{responsiveId:"",id:9,avatar:Y,fullName:"Allyson Moakler",post:"Safety Technician",email:"amoakler8@shareasale.com",city:"Mogilany",startDate:"12/29/2018",salary:11677.32,age:"39",experience:"9 Years",status:"Applied"},{responsiveId:"",id:11,avatar:"",fullName:"De Falloon",post:"Sales Representative",email:"dfalloona@ifeng.com",city:"Colima",startDate:"06/12/2018",salary:19252.12,age:"30",experience:"0 Year",status:"Resigned"},{responsiveId:"",id:12,avatar:"",fullName:"Cyrus Gornal",post:"Senior Sales Associate",email:"cgornalb@fda.gov",city:"Boro Utara",startDate:"12/09/2017",salary:16745.47,age:"22",experience:"2 Years",status:"Resigned"},{responsiveId:"",id:13,avatar:"",fullName:"Tallou Balf",post:"Staff Accountant",email:"tbalfc@sina.com.cn",city:"Siliana",startDate:"01/21/2016",salary:15488.53,age:"36",experience:"6 Years",status:"Resigned"},{responsiveId:"",id:15,avatar:"",fullName:"Wilmar Bourton",post:"Administrative Assistant",email:"wbourtone@sakura.ne.jp",city:"Bích Động",startDate:"04/25/2018",salary:13304.45,age:"19",experience:"9 Years",status:"Applied"},{responsiveId:"",id:16,avatar:S,fullName:"Robinson Brazenor",post:"General Manager",email:"rbrazenorf@symantec.com",city:"Gendiwu",startDate:"12/23/2017",salary:11953.08,age:"66",experience:"6 Years",status:"Applied"},{responsiveId:"",id:17,avatar:"",fullName:"Nadia Bettenson",post:"Environmental Tech",email:"nbettensong@joomla.org",city:"Chabařovice",startDate:"07/11/2018",salary:20484.44,age:"64",experience:"4 Years",status:"Current"},{responsiveId:"",id:18,avatar:"",fullName:"Titus Hayne",post:"Web Designer",email:"thayneh@kickstarter.com",city:"Yangon",startDate:"05/25/2019",salary:16871.48,age:"59",experience:"9 Years",status:"Current"},{responsiveId:"",id:19,avatar:S,fullName:"Roxie Huck",post:"Administrative Assistant",email:"rhucki@ed.gov",city:"Polýkastro",startDate:"04/04/2019",salary:19653.56,age:"41",experience:"1 Year",status:"Resigned"},{responsiveId:"",id:23,avatar:f,fullName:"Rosmunda Steed",post:"Assistant Media Planner",email:"rsteedm@xing.com",city:"Manzanares",startDate:"12/23/2017",salary:13778.34,age:"21",experience:"1 Year",status:"Applied"},{responsiveId:"",id:26,avatar:Y,fullName:"Morgen Benes",post:"Senior Sales Associate",email:"mbenesp@ted.com",city:"Cà Mau",startDate:"04/10/2016",salary:16969.63,age:"42",experience:"2 Years",status:"Resigned"},{responsiveId:"",id:28,avatar:"",fullName:"Kliment McGinney",post:"Chief Design Engineer",email:"kmcginneyr@paginegialle.it",city:"Xiaocheng",startDate:"07/09/2018",salary:24027.81,age:"28",experience:"8 Years",status:"Resigned"},{responsiveId:"",id:31,avatar:"",fullName:"Teressa Bleakman",post:"Senior Editor",email:"tbleakmanu@phpbb.com",city:"Žebrák",startDate:"09/03/2016",salary:24875.41,age:"37",experience:"7 Years",status:"Applied"}],n=[{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"}],r=[{key:"status"}],i=p=>p==="Current"?{color:"primary"}:p==="Professional"?{color:"success"}:p==="Rejected"?{color:"error"}:p==="Resigned"?{color:"warning"}:{color:"info"},t=p=>p.icon;return(p,v)=>(u(),y(C,{headers:n,items:m,"items-per-page":10,"group-by":r,class:"text-no-wrap"},{"item.fullName":a(({item:g})=>[s("div",Oe,[e(_,{size:"32",color:g.avatar?"":"primary",class:M(g.avatar?"":"v-avatar-light-bg primary--text"),variant:g.avatar?void 0:"tonal"},{default:a(()=>[g.avatar?(u(),y(T,{key:0,src:g.avatar},null,8,["src"])):(u(),A("span",Ue,l(("avatarText"in p?p.avatarText:o(z))(g.fullName)),1))]),_:2},1032,["color","class","variant"]),s("div",He,[s("span",Fe,l(g.fullName),1),s("small",null,l(g.post),1)])])]),"item.status":a(({item:g})=>[e(P,{color:i(g.status).color,size:"small",class:"font-weight-medium"},{default:a(()=>[b(l(g.status),1)]),_:2},1032,["color"])]),"data-table-group":a(({props:g,item:E,count:D})=>[s("td",null,[e($,de(g,{variant:"text",density:"comfortable"}),{default:a(()=>[e(O,{class:"flip-in-rtl",icon:t(g)},null,8,["icon"])]),_:2},1040),s("span",null,l(E.value),1),s("span",null,"("+l(D)+")",1)])]),_:1}))}},We={class:"v-data-table__tr"},Je=["colspan"],qe={class:"my-1"},Xe={class:"my-1"},Qe={class:"d-flex align-center"},Ze={key:1,class:"text-sm"},ea={class:"d-flex flex-column ms-3"},aa={class:"d-block font-weight-medium text-high-emphasis text-truncate"},ta={__name:"DemoDataTableExpandableRows",setup(I){const m=[{title:"",key:"data-table-expand"},{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"}],n=r=>r===1?{color:"primary",text:"Current"}:r===2?{color:"success",text:"Professional"}:r===3?{color:"error",text:"Rejected"}:r===4?{color:"warning",text:"Resigned"}:{color:"info",text:"Applied"};return(r,i)=>(u(),y(C,{headers:m,items:o(B),"items-per-page":5,class:"text-no-wrap","expand-on-click":""},{"expanded-row":a(t=>[s("tr",We,[s("td",{colspan:m.length},[s("p",qe," City: "+l(t.item.city),1),s("p",Xe," Experience: "+l(t.item.experience),1),s("p",null,"Post: "+l(t.item.post),1)],8,Je)])]),"item.fullName":a(({item:t})=>[s("div",Qe,[e(_,{size:"32",color:t.avatar?"":"primary",class:M(t.avatar?"":"v-avatar-light-bg primary--text"),variant:t.avatar?void 0:"tonal"},{default:a(()=>[t.avatar?(u(),y(T,{key:0,src:t.avatar},null,8,["src"])):(u(),A("span",Ze,l(("avatarText"in r?r.avatarText:o(z))(t.fullName)),1))]),_:2},1032,["color","class","variant"]),s("div",ea,[s("span",aa,l(t.fullName),1),s("small",null,l(t.post),1)])])]),"item.status":a(({item:t})=>[e(P,{color:n(t.status).color,class:"font-weight-medium",size:"small"},{default:a(()=>[b(l(n(t.status).text),1)]),_:2},1032,["color"])]),_:1},8,["items"]))}},sa={class:"d-flex align-center"},ra={key:1,class:"text-sm"},ia={class:"d-flex flex-column ms-3"},la={class:"d-block font-weight-medium text-high-emphasis text-truncate"},oa={__name:"DemoDataTableFixedHeader",setup(I){const m=[{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"}],n=r=>r===1?{color:"primary",text:"Current"}:r===2?{color:"success",text:"Professional"}:r===3?{color:"error",text:"Rejected"}:r===4?{color:"warning",text:"Resigned"}:{color:"info",text:"Applied"};return(r,i)=>(u(),y(C,{headers:m,items:o(B),"items-per-page":10,height:"300",class:"text-no-wrap","fixed-header":""},{"item.fullName":a(({item:t})=>[s("div",sa,[e(_,{size:"32",color:t.avatar?"":"primary",class:M(t.avatar?"":"v-avatar-light-bg primary--text"),variant:t.avatar?void 0:"tonal"},{default:a(()=>[t.avatar?(u(),y(T,{key:0,src:t.avatar},null,8,["src"])):(u(),A("span",ra,l(("avatarText"in r?r.avatarText:o(z))(t.fullName)),1))]),_:2},1032,["color","class","variant"]),s("div",ia,[s("span",la,l(t.fullName),1),s("small",null,l(t.post),1)])])]),"item.status":a(({item:t})=>[e(P,{color:n(t.status).color,class:"font-weight-medium",size:"small"},{default:a(()=>[b(l(n(t.status).text),1)]),_:2},1032,["color"])]),_:1},8,["items"]))}},na={class:"d-flex align-center"},ca={key:1,class:"text-sm"},ma={class:"d-flex flex-column ms-3"},da={class:"d-block font-weight-medium text-high-emphasis text-truncate"},ua={__name:"DemoDataTableRowSelection",setup(I){const m=[{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"}],n=r=>r===1?{color:"primary",text:"Current"}:r===2?{color:"success",text:"Professional"}:r===3?{color:"error",text:"Rejected"}:r===4?{color:"warning",text:"Resigned"}:{color:"info",text:"Applied"};return(r,i)=>(u(),y(C,{headers:m,items:o(B),"items-per-page":5,"show-select":"",class:"text-no-wrap"},{"item.fullName":a(({item:t})=>[s("div",na,[e(_,{size:"32",color:t.avatar?"":"primary",class:M(t.avatar?"":"v-avatar-light-bg primary--text"),variant:t.avatar?void 0:"tonal"},{default:a(()=>[t.avatar?(u(),y(T,{key:0,src:t.avatar},null,8,["src"])):(u(),A("span",ca,l(("avatarText"in r?r.avatarText:o(z))(t.fullName)),1))]),_:2},1032,["color","class","variant"]),s("div",ma,[s("span",da,l(t.fullName),1),s("small",null,l(t.post),1)])])]),"item.status":a(({item:t})=>[e(P,{color:n(t.status).color,class:"font-weight-medium",size:"small"},{default:a(()=>[b(l(n(t.status).text),1)]),_:2},1032,["color"])]),_:1},8,["items"]))}},pa={class:"d-flex align-center"},va={key:1,class:"text-sm"},ga={class:"d-flex flex-column ms-3"},ya={class:"d-block font-weight-medium text-high-emphasis text-truncate"},fa={__name:"DemoDataTableCellSlot",setup(I){const m=[{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"SALARY",key:"salary"},{title:"AGE",key:"age"},{title:"STATUS",key:"status"}],n=r=>r===1?{color:"primary",text:"Current"}:r===2?{color:"success",text:"Professional"}:r===3?{color:"error",text:"Rejected"}:r===4?{color:"warning",text:"Resigned"}:{color:"info",text:"Applied"};return(r,i)=>(u(),y(C,{headers:m,items:o(B),"items-per-page":5,class:"text-no-wrap"},{"item.fullName":a(({item:t})=>[s("div",pa,[e(_,{size:"32",color:t.avatar?"":"primary",class:M(t.avatar?"":"v-avatar-light-bg primary--text"),variant:t.avatar?void 0:"tonal"},{default:a(()=>[t.avatar?(u(),y(T,{key:0,src:t.avatar},null,8,["src"])):(u(),A("span",va,l(("avatarText"in r?r.avatarText:o(z))(t.fullName)),1))]),_:2},1032,["color","class","variant"]),s("div",ga,[s("span",ya,l(t.fullName),1),s("small",null,l(t.post),1)])])]),"item.status":a(({item:t})=>[e(P,{color:n(t.status).color,class:"font-weight-medium",size:"small"},{default:a(()=>[b(l(n(t.status).text),1)]),_:2},1032,["color"])]),_:1},8,["items"]))}},xa={__name:"DemoDataTableDense",setup(I){const m=[{title:"ID",key:"id"},{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"EXPERIENCE",key:"experience"},{title:"AGE",key:"age"}];return(n,r)=>(u(),y(C,{headers:m,items:o(B),density:"compact","items-per-page":5,class:"text-no-wrap"},null,8,["items"]))}},ha={class:"text-h6"},Da={__name:"DemoDataTableBasic",setup(I){const m=[{title:"ID",key:"id"},{title:"NAME",key:"fullName"},{title:"EMAIL",key:"email"},{title:"DATE",key:"startDate"},{title:"EXPERIENCE",key:"experience"},{title:"AGE",key:"age"}];return(n,r)=>(u(),y(C,{headers:m,items:o(B),"items-per-page":5,class:"text-no-wrap"},{"item.id":a(({item:i})=>[s("span",ha,l(i.id),1)]),_:1},8,["items"]))}},ba={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'EXPERIENCE', key: 'experience' },
  { title: 'AGE', key: 'age' },
]
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    class="text-no-wrap"
  >
    <template #item.id="{ item }">
      <span class="text-h6">{{ item.id }}</span>
    </template>
  </VDataTable>
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  {
    title: 'ID',
    key: 'id',
  },
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'EXPERIENCE',
    key: 'experience',
  },
  {
    title: 'AGE',
    key: 'age',
  },
]
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    class="text-no-wrap"
  >
    <template #item.id="{ item }">
      <span class="text-h6">{{ item.id }}</span>
    </template>
  </VDataTable>
</template>
`},Va={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'SALARY', key: 'salary' },
  { title: 'AGE', key: 'age' },
  { title: 'STATUS', key: 'status' },
]

const resolveStatusVariant = (status: number) => {
  if (status === 1)
    return { color: 'primary', text: 'Current' }
  else if (status === 2)
    return { color: 'success', text: 'Professional' }
  else if (status === 3)
    return { color: 'error', text: 'Rejected' }
  else if (status === 4)
    return { color: 'warning', text: 'Resigned' }
  else
    return { color: 'info', text: 'Applied' }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'SALARY',
    key: 'salary',
  },
  {
    title: 'AGE',
    key: 'age',
  },
  {
    title: 'STATUS',
    key: 'status',
  },
]

const resolveStatusVariant = status => {
  if (status === 1)
    return {
      color: 'primary',
      text: 'Current',
    }
  else if (status === 2)
    return {
      color: 'success',
      text: 'Professional',
    }
  else if (status === 3)
    return {
      color: 'error',
      text: 'Rejected',
    }
  else if (status === 4)
    return {
      color: 'warning',
      text: 'Resigned',
    }
  else
    return {
      color: 'info',
      text: 'Applied',
    }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`},ka={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'EXPERIENCE', key: 'experience' },
  { title: 'AGE', key: 'age' },
]
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    density="compact"
    :items-per-page="5"
    class="text-no-wrap"
  />
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  {
    title: 'ID',
    key: 'id',
  },
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'EXPERIENCE',
    key: 'experience',
  },
  {
    title: 'AGE',
    key: 'age',
  },
]
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    density="compact"
    :items-per-page="5"
    class="text-no-wrap"
  />
</template>
`},Aa={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'

// Headers
const headers = [
  { title: '', key: 'data-table-expand' },
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'SALARY', key: 'salary' },
  { title: 'AGE', key: 'age' },
  { title: 'STATUS', key: 'status' },
]

const resolveStatusVariant = (status: number) => {
  if (status === 1)
    return { color: 'primary', text: 'Current' }
  else if (status === 2)
    return { color: 'success', text: 'Professional' }
  else if (status === 3)
    return { color: 'error', text: 'Rejected' }
  else if (status === 4)
    return { color: 'warning', text: 'Resigned' }
  else
    return { color: 'info', text: 'Applied' }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    class="text-no-wrap"
    expand-on-click
  >
    <!-- Expanded Row Data -->
    <template #expanded-row="slotProps">
      <tr class="v-data-table__tr">
        <td :colspan="headers.length">
          <p class="my-1">
            City: {{ slotProps.item.city }}
          </p>
          <p class="my-1">
            Experience: {{ slotProps.item.experience }}
          </p>
          <p>Post: {{ slotProps.item.post }}</p>
        </td>
      </tr>
    </template>

    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

// Headers
const headers = [
  {
    title: '',
    key: 'data-table-expand',
  },
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'SALARY',
    key: 'salary',
  },
  {
    title: 'AGE',
    key: 'age',
  },
  {
    title: 'STATUS',
    key: 'status',
  },
]

const resolveStatusVariant = status => {
  if (status === 1)
    return {
      color: 'primary',
      text: 'Current',
    }
  else if (status === 2)
    return {
      color: 'success',
      text: 'Professional',
    }
  else if (status === 3)
    return {
      color: 'error',
      text: 'Rejected',
    }
  else if (status === 4)
    return {
      color: 'warning',
      text: 'Resigned',
    }
  else
    return {
      color: 'info',
      text: 'Applied',
    }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    class="text-no-wrap"
    expand-on-click
  >
    <!-- Expanded Row Data -->
    <template #expanded-row="slotProps">
      <tr class="v-data-table__tr">
        <td :colspan="headers.length">
          <p class="my-1">
            City: {{ slotProps.item.city }}
          </p>
          <p class="my-1">
            Experience: {{ slotProps.item.experience }}
          </p>
          <p>Post: {{ slotProps.item.post }}</p>
        </td>
      </tr>
    </template>

    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`},Ia={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'
import type { Data } from '@db/pages/datatable/types'

const userList = ref<Data[]>([])
const options = ref({ page: 1, itemsPerPage: 5, sortBy: [''], sortDesc: [false] })

// headers
const headers = [
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'SALARY', key: 'salary' },
  { title: 'AGE', key: 'age' },
  { title: 'STATUS', key: 'status' },
]

const resolveStatusVariant = (status: number) => {
  if (status === 1)
    return { color: 'primary', text: 'Current' }
  else if (status === 2)
    return { color: 'success', text: 'Professional' }
  else if (status === 3)
    return { color: 'error', text: 'Rejected' }
  else if (status === 4)
    return { color: 'warning', text: 'Resigned' }
  else
    return { color: 'info', text: 'Applied' }
}

onMounted(() => {
  userList.value = JSON.parse(JSON.stringify(data))
})
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="userList"
    :items-per-page="options.itemsPerPage"
    :page="options.page"
    :options="options"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>

    <template #bottom>
      <VCardText class="pt-2">
        <div class="d-flex flex-wrap justify-center justify-sm-space-between gap-y-2 mt-2">
          <VTextField
            v-model="options.itemsPerPage"
            label="Rows per page:"
            type="number"
            min="1"
            max="15"
            hide-details
            variant="underlined"
            style="max-inline-size: 8rem;min-inline-size: 5rem;"
          />

          <VPagination
            v-model="options.page"
            :total-visible="$vuetify.display.smAndDown ? 2 : 3"
            :length="Math.ceil(userList.length / options.itemsPerPage)"
          />
        </div>
      </VCardText>
    </template>
  </VDataTable>
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

const userList = ref([])

const options = ref({
  page: 1,
  itemsPerPage: 5,
  sortBy: [''],
  sortDesc: [false],
})

// headers
const headers = [
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'SALARY',
    key: 'salary',
  },
  {
    title: 'AGE',
    key: 'age',
  },
  {
    title: 'STATUS',
    key: 'status',
  },
]

const resolveStatusVariant = status => {
  if (status === 1)
    return {
      color: 'primary',
      text: 'Current',
    }
  else if (status === 2)
    return {
      color: 'success',
      text: 'Professional',
    }
  else if (status === 3)
    return {
      color: 'error',
      text: 'Rejected',
    }
  else if (status === 4)
    return {
      color: 'warning',
      text: 'Resigned',
    }
  else
    return {
      color: 'info',
      text: 'Applied',
    }
}

onMounted(() => {
  userList.value = JSON.parse(JSON.stringify(data))
})
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="userList"
    :items-per-page="options.itemsPerPage"
    :page="options.page"
    :options="options"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>

    <template #bottom>
      <VCardText class="pt-2">
        <div class="d-flex flex-wrap justify-center justify-sm-space-between gap-y-2 mt-2">
          <VTextField
            v-model="options.itemsPerPage"
            label="Rows per page:"
            type="number"
            min="1"
            max="15"
            hide-details
            variant="underlined"
            style="max-inline-size: 8rem;min-inline-size: 5rem;"
          />

          <VPagination
            v-model="options.page"
            :total-visible="$vuetify.display.smAndDown ? 2 : 3"
            :length="Math.ceil(userList.length / options.itemsPerPage)"
          />
        </div>
      </VCardText>
    </template>
  </VDataTable>
</template>
`},Na={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'SALARY', key: 'salary' },
  { title: 'AGE', key: 'age' },
  { title: 'STATUS', key: 'status' },
]

const resolveStatusVariant = (status: number) => {
  if (status === 1)
    return { color: 'primary', text: 'Current' }
  else if (status === 2)
    return { color: 'success', text: 'Professional' }
  else if (status === 3)
    return { color: 'error', text: 'Rejected' }
  else if (status === 4)
    return { color: 'warning', text: 'Resigned' }
  else
    return { color: 'info', text: 'Applied' }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="10"
    height="300"
    class="text-no-wrap"
    fixed-header
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'SALARY',
    key: 'salary',
  },
  {
    title: 'AGE',
    key: 'age',
  },
  {
    title: 'STATUS',
    key: 'status',
  },
]

const resolveStatusVariant = status => {
  if (status === 1)
    return {
      color: 'primary',
      text: 'Current',
    }
  else if (status === 2)
    return {
      color: 'success',
      text: 'Professional',
    }
  else if (status === 3)
    return {
      color: 'error',
      text: 'Rejected',
    }
  else if (status === 4)
    return {
      color: 'warning',
      text: 'Resigned',
    }
  else
    return {
      color: 'info',
      text: 'Applied',
    }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="10"
    height="300"
    class="text-no-wrap"
    fixed-header
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`},Sa={ts:`<script setup lang="ts">
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar7 from '@images/avatars/avatar-7.png'
import avatar8 from '@images/avatars/avatar-8.png'

const userList = [
  {
    responsiveId: '',
    id: 1,
    avatar: avatar8,
    fullName: 'Korrie O'Crevy',
    post: 'Nuclear Power Engineer',
    email: 'kocrevy0@thetimes.co.uk',
    city: 'Krasnosilka',
    startDate: '09/23/2016',
    salary: 23896.35,
    age: '61',
    experience: '1 Year',
    status: 'Professional',
  },
  {
    responsiveId: '',
    id: 2,
    avatar: avatar1,
    fullName: 'Bailie Coulman',
    post: 'VP Quality Control',
    email: 'bcoulman1@yolasite.com',
    city: 'Hinigaran',
    startDate: '05/20/2018',
    salary: 13633.69,
    age: '63',
    experience: '3 Years',
    status: 'Professional',
  },
  {
    responsiveId: '',
    id: 3,
    avatar: avatar7,
    fullName: 'Stella Ganderton',
    post: 'Operator',
    email: 'sganderton2@tuttocitta.it',
    city: 'Golcowa',
    startDate: '03/24/2018',
    salary: 13076.28,
    age: '66',
    experience: '6 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 4,
    avatar: avatar8,
    fullName: 'Dorolice Crossman',
    post: 'Cost Accountant',
    email: 'dcrossman3@google.co.jp',
    city: 'Paquera',
    startDate: '12/03/2017',
    salary: 12336.17,
    age: '22',
    experience: '2 Years',
    status: 'Professional',
  },
  {
    responsiveId: '',
    id: 6,
    avatar: '',
    fullName: 'Genevra Honeywood',
    post: 'Geologist',
    email: 'ghoneywood5@narod.ru',
    city: 'Maofan',
    startDate: '06/01/2017',
    salary: 17803.8,
    age: '61',
    experience: '1 Year',
    status: 'Current',
  },
  {
    responsiveId: '',
    id: 7,
    avatar: '',
    fullName: 'Eileen Diehn',
    post: 'Environmental Specialist',
    email: 'ediehn6@163.com',
    city: 'Lampuyang',
    startDate: '10/15/2017',
    salary: 18991.67,
    age: '59',
    experience: '9 Years',
    status: 'Rejected',
  },
  {
    responsiveId: '',
    id: 8,
    avatar: avatar7,
    fullName: 'Richardo Aldren',
    post: 'Senior Sales Associate',
    email: 'raldren7@mtv.com',
    city: 'Skoghall',
    startDate: '11/05/2016',
    salary: 19230.13,
    age: '55',
    experience: '5 Years',
    status: 'Rejected',
  },
  {
    responsiveId: '',
    id: 9,
    avatar: avatar2,
    fullName: 'Allyson Moakler',
    post: 'Safety Technician',
    email: 'amoakler8@shareasale.com',
    city: 'Mogilany',
    startDate: '12/29/2018',
    salary: 11677.32,
    age: '39',
    experience: '9 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 11,
    avatar: '',
    fullName: 'De Falloon',
    post: 'Sales Representative',
    email: 'dfalloona@ifeng.com',
    city: 'Colima',
    startDate: '06/12/2018',
    salary: 19252.12,
    age: '30',
    experience: '0 Year',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 12,
    avatar: '',
    fullName: 'Cyrus Gornal',
    post: 'Senior Sales Associate',
    email: 'cgornalb@fda.gov',
    city: 'Boro Utara',
    startDate: '12/09/2017',
    salary: 16745.47,
    age: '22',
    experience: '2 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 13,
    avatar: '',
    fullName: 'Tallou Balf',
    post: 'Staff Accountant',
    email: 'tbalfc@sina.com.cn',
    city: 'Siliana',
    startDate: '01/21/2016',
    salary: 15488.53,
    age: '36',
    experience: '6 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 15,
    avatar: '',
    fullName: 'Wilmar Bourton',
    post: 'Administrative Assistant',
    email: 'wbourtone@sakura.ne.jp',
    city: 'Bích Động',
    startDate: '04/25/2018',
    salary: 13304.45,
    age: '19',
    experience: '9 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 16,
    avatar: avatar4,
    fullName: 'Robinson Brazenor',
    post: 'General Manager',
    email: 'rbrazenorf@symantec.com',
    city: 'Gendiwu',
    startDate: '12/23/2017',
    salary: 11953.08,
    age: '66',
    experience: '6 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 17,
    avatar: '',
    fullName: 'Nadia Bettenson',
    post: 'Environmental Tech',
    email: 'nbettensong@joomla.org',
    city: 'Chabařovice',
    startDate: '07/11/2018',
    salary: 20484.44,
    age: '64',
    experience: '4 Years',
    status: 'Current',
  },
  {
    responsiveId: '',
    id: 18,
    avatar: '',
    fullName: 'Titus Hayne',
    post: 'Web Designer',
    email: 'thayneh@kickstarter.com',
    city: 'Yangon',
    startDate: '05/25/2019',
    salary: 16871.48,
    age: '59',
    experience: '9 Years',
    status: 'Current',
  },
  {
    responsiveId: '',
    id: 19,
    avatar: avatar4,
    fullName: 'Roxie Huck',
    post: 'Administrative Assistant',
    email: 'rhucki@ed.gov',
    city: 'Polýkastro',
    startDate: '04/04/2019',
    salary: 19653.56,
    age: '41',
    experience: '1 Year',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 23,
    avatar: avatar7,
    fullName: 'Rosmunda Steed',
    post: 'Assistant Media Planner',
    email: 'rsteedm@xing.com',
    city: 'Manzanares',
    startDate: '12/23/2017',
    salary: 13778.34,
    age: '21',
    experience: '1 Year',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 26,
    avatar: avatar2,
    fullName: 'Morgen Benes',
    post: 'Senior Sales Associate',
    email: 'mbenesp@ted.com',
    city: 'Cà Mau',
    startDate: '04/10/2016',
    salary: 16969.63,
    age: '42',
    experience: '2 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 28,
    avatar: '',
    fullName: 'Kliment McGinney',
    post: 'Chief Design Engineer',
    email: 'kmcginneyr@paginegialle.it',
    city: 'Xiaocheng',
    startDate: '07/09/2018',
    salary: 24027.81,
    age: '28',
    experience: '8 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 31,
    avatar: '',
    fullName: 'Teressa Bleakman',
    post: 'Senior Editor',
    email: 'tbleakmanu@phpbb.com',
    city: 'Žebrák',
    startDate: '09/03/2016',
    salary: 24875.41,
    age: '37',
    experience: '7 Years',
    status: 'Applied',
  },
]

const headers = [
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'SALARY', key: 'salary' },
  { title: 'AGE', key: 'age' },
  { title: 'STATUS', key: 'status' },
]

const groupBy = [{ key: 'status' }]

const resolveStatusVariant = (status: string) => {
  if (status === 'Current')
    return { color: 'primary' }
  else if (status === 'Professional')
    return { color: 'success' }
  else if (status === 'Rejected')
    return { color: 'error' }
  else if (status === 'Resigned')
    return { color: 'warning' }
  else
    return { color: 'info' }
}

const getIcon = (props: Record<string, unknown>) => props.icon as any
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="userList"
    :items-per-page="10"
    :group-by="groupBy"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        size="small"
        class="font-weight-medium"
      >
        {{ item.status }}
      </VChip>
    </template>

    <template #data-table-group="{ props, item, count }">
      <td>
        <VBtn
          v-bind="props"
          variant="text"
          density="comfortable"
        >
          <VIcon
            class="flip-in-rtl"
            :icon="getIcon(props)"
          />
        </VBtn>

        <span>{{ item.value }}</span>
        <span>({{ count }})</span>
      </td>
    </template>
  </VDataTable>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar7 from '@images/avatars/avatar-7.png'
import avatar8 from '@images/avatars/avatar-8.png'

const userList = [
  {
    responsiveId: '',
    id: 1,
    avatar: avatar8,
    fullName: 'Korrie O'Crevy',
    post: 'Nuclear Power Engineer',
    email: 'kocrevy0@thetimes.co.uk',
    city: 'Krasnosilka',
    startDate: '09/23/2016',
    salary: 23896.35,
    age: '61',
    experience: '1 Year',
    status: 'Professional',
  },
  {
    responsiveId: '',
    id: 2,
    avatar: avatar1,
    fullName: 'Bailie Coulman',
    post: 'VP Quality Control',
    email: 'bcoulman1@yolasite.com',
    city: 'Hinigaran',
    startDate: '05/20/2018',
    salary: 13633.69,
    age: '63',
    experience: '3 Years',
    status: 'Professional',
  },
  {
    responsiveId: '',
    id: 3,
    avatar: avatar7,
    fullName: 'Stella Ganderton',
    post: 'Operator',
    email: 'sganderton2@tuttocitta.it',
    city: 'Golcowa',
    startDate: '03/24/2018',
    salary: 13076.28,
    age: '66',
    experience: '6 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 4,
    avatar: avatar8,
    fullName: 'Dorolice Crossman',
    post: 'Cost Accountant',
    email: 'dcrossman3@google.co.jp',
    city: 'Paquera',
    startDate: '12/03/2017',
    salary: 12336.17,
    age: '22',
    experience: '2 Years',
    status: 'Professional',
  },
  {
    responsiveId: '',
    id: 6,
    avatar: '',
    fullName: 'Genevra Honeywood',
    post: 'Geologist',
    email: 'ghoneywood5@narod.ru',
    city: 'Maofan',
    startDate: '06/01/2017',
    salary: 17803.8,
    age: '61',
    experience: '1 Year',
    status: 'Current',
  },
  {
    responsiveId: '',
    id: 7,
    avatar: '',
    fullName: 'Eileen Diehn',
    post: 'Environmental Specialist',
    email: 'ediehn6@163.com',
    city: 'Lampuyang',
    startDate: '10/15/2017',
    salary: 18991.67,
    age: '59',
    experience: '9 Years',
    status: 'Rejected',
  },
  {
    responsiveId: '',
    id: 8,
    avatar: avatar7,
    fullName: 'Richardo Aldren',
    post: 'Senior Sales Associate',
    email: 'raldren7@mtv.com',
    city: 'Skoghall',
    startDate: '11/05/2016',
    salary: 19230.13,
    age: '55',
    experience: '5 Years',
    status: 'Rejected',
  },
  {
    responsiveId: '',
    id: 9,
    avatar: avatar2,
    fullName: 'Allyson Moakler',
    post: 'Safety Technician',
    email: 'amoakler8@shareasale.com',
    city: 'Mogilany',
    startDate: '12/29/2018',
    salary: 11677.32,
    age: '39',
    experience: '9 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 11,
    avatar: '',
    fullName: 'De Falloon',
    post: 'Sales Representative',
    email: 'dfalloona@ifeng.com',
    city: 'Colima',
    startDate: '06/12/2018',
    salary: 19252.12,
    age: '30',
    experience: '0 Year',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 12,
    avatar: '',
    fullName: 'Cyrus Gornal',
    post: 'Senior Sales Associate',
    email: 'cgornalb@fda.gov',
    city: 'Boro Utara',
    startDate: '12/09/2017',
    salary: 16745.47,
    age: '22',
    experience: '2 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 13,
    avatar: '',
    fullName: 'Tallou Balf',
    post: 'Staff Accountant',
    email: 'tbalfc@sina.com.cn',
    city: 'Siliana',
    startDate: '01/21/2016',
    salary: 15488.53,
    age: '36',
    experience: '6 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 15,
    avatar: '',
    fullName: 'Wilmar Bourton',
    post: 'Administrative Assistant',
    email: 'wbourtone@sakura.ne.jp',
    city: 'Bích Động',
    startDate: '04/25/2018',
    salary: 13304.45,
    age: '19',
    experience: '9 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 16,
    avatar: avatar4,
    fullName: 'Robinson Brazenor',
    post: 'General Manager',
    email: 'rbrazenorf@symantec.com',
    city: 'Gendiwu',
    startDate: '12/23/2017',
    salary: 11953.08,
    age: '66',
    experience: '6 Years',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 17,
    avatar: '',
    fullName: 'Nadia Bettenson',
    post: 'Environmental Tech',
    email: 'nbettensong@joomla.org',
    city: 'Chabařovice',
    startDate: '07/11/2018',
    salary: 20484.44,
    age: '64',
    experience: '4 Years',
    status: 'Current',
  },
  {
    responsiveId: '',
    id: 18,
    avatar: '',
    fullName: 'Titus Hayne',
    post: 'Web Designer',
    email: 'thayneh@kickstarter.com',
    city: 'Yangon',
    startDate: '05/25/2019',
    salary: 16871.48,
    age: '59',
    experience: '9 Years',
    status: 'Current',
  },
  {
    responsiveId: '',
    id: 19,
    avatar: avatar4,
    fullName: 'Roxie Huck',
    post: 'Administrative Assistant',
    email: 'rhucki@ed.gov',
    city: 'Polýkastro',
    startDate: '04/04/2019',
    salary: 19653.56,
    age: '41',
    experience: '1 Year',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 23,
    avatar: avatar7,
    fullName: 'Rosmunda Steed',
    post: 'Assistant Media Planner',
    email: 'rsteedm@xing.com',
    city: 'Manzanares',
    startDate: '12/23/2017',
    salary: 13778.34,
    age: '21',
    experience: '1 Year',
    status: 'Applied',
  },
  {
    responsiveId: '',
    id: 26,
    avatar: avatar2,
    fullName: 'Morgen Benes',
    post: 'Senior Sales Associate',
    email: 'mbenesp@ted.com',
    city: 'Cà Mau',
    startDate: '04/10/2016',
    salary: 16969.63,
    age: '42',
    experience: '2 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 28,
    avatar: '',
    fullName: 'Kliment McGinney',
    post: 'Chief Design Engineer',
    email: 'kmcginneyr@paginegialle.it',
    city: 'Xiaocheng',
    startDate: '07/09/2018',
    salary: 24027.81,
    age: '28',
    experience: '8 Years',
    status: 'Resigned',
  },
  {
    responsiveId: '',
    id: 31,
    avatar: '',
    fullName: 'Teressa Bleakman',
    post: 'Senior Editor',
    email: 'tbleakmanu@phpbb.com',
    city: 'Žebrák',
    startDate: '09/03/2016',
    salary: 24875.41,
    age: '37',
    experience: '7 Years',
    status: 'Applied',
  },
]

const headers = [
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'SALARY',
    key: 'salary',
  },
  {
    title: 'AGE',
    key: 'age',
  },
  {
    title: 'STATUS',
    key: 'status',
  },
]

const groupBy = [{ key: 'status' }]

const resolveStatusVariant = status => {
  if (status === 'Current')
    return { color: 'primary' }
  else if (status === 'Professional')
    return { color: 'success' }
  else if (status === 'Rejected')
    return { color: 'error' }
  else if (status === 'Resigned')
    return { color: 'warning' }
  else
    return { color: 'info' }
}

const getIcon = props => props.icon
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="userList"
    :items-per-page="10"
    :group-by="groupBy"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        size="small"
        class="font-weight-medium"
      >
        {{ item.status }}
      </VChip>
    </template>

    <template #data-table-group="{ props, item, count }">
      <td>
        <VBtn
          v-bind="props"
          variant="text"
          density="comfortable"
        >
          <VIcon
            class="flip-in-rtl"
            :icon="getIcon(props)"
          />
        </VBtn>

        <span>{{ item.value }}</span>
        <span>({{ count }})</span>
      </td>
    </template>
  </VDataTable>
</template>
`},Ca={ts:`<script setup lang="ts">
import type { SalesDetails } from '@db/pages/datatable/types'

const { data: productList } = await useApi<SalesDetails[]>('pages/datatable')

const search = ref('')

// headers
const headers = [
  { title: 'PRODUCT', key: 'product.name' },
  { title: 'DATE', key: 'date' },
  { title: 'CATEGORY', key: 'product.category' },
  { title: 'BUYERS', key: 'buyer.name' },
  { title: 'PAYMENT', key: 'payment', sortable: false },
  { title: 'STATUS', key: 'status', sortable: false },
  { title: 'DELETE', key: 'delete', sortable: false },
]

// 👉 methods
const deleteItem = (itemId: number) => {
  if (!productList.value)
    return

  const index = productList.value.findIndex(item => item.product.id === itemId)

  productList.value.splice(index, 1)
}

const categoryIcons = [
  { name: 'Mouse', icon: 'ri-mouse-fill', color: 'warning' },
  { name: 'Glass', icon: 'ri-glasses-line', color: 'primary' },
  { name: 'Smart Watch', icon: 'ri-time-line', color: 'success' },
  { name: 'Bag', icon: 'ri-shopping-bag-line', color: 'info' },
  { name: 'Storage Device', icon: 'ri-tape-line', color: 'warning' },
  { name: 'Bluetooth', icon: 'ri-bluetooth-line', color: 'error' },
  { name: 'Gaming', icon: 'ri-gamepad-line', color: 'warning' },
  { name: 'Home', icon: 'ri-home-line', color: 'error' },
  { name: 'VR', icon: 'ri-goggles-line', color: 'primary' },
  { name: 'Shoes', icon: 'ri-omega', color: 'success' },
  { name: 'Electronics', icon: 'ri-flashlight-fill', color: 'info' },
  { name: 'Projector', icon: 'ri-projector-line', color: 'warning' },
  { name: 'iPod', icon: 'ri-music-line', color: 'error' },
  { name: 'Keyboard', icon: 'ri-keyboard-box-line', color: 'primary' },
  { name: 'Smart Phone', icon: 'ri-smartphone-line', color: 'success' },
  { name: 'Smart TV', icon: 'ri-tv-line', color: 'info' },
  { name: 'Google Home', icon: 'ri-google-line', color: 'warning' },
  { name: 'Mac', icon: 'ri-apple-line', color: 'error' },
  { name: 'Headphone', icon: 'ri-headphone-line', color: 'primary' },
  { name: 'iMac', icon: 'ri-computer-line', color: 'success' },
  { name: 'iPhone', icon: 'ri-apple-line', color: 'warning' },
]

const resolveStatusColor = (status: string) => {
  if (status === 'Confirmed')
    return 'primary'
  if (status === 'Completed')
    return 'success'
  if (status === 'Cancelled')
    return 'error'
}

const categoryIconFilter = (categoryName: string): {
  icon: string
  color: string }[] => {
  const index = categoryIcons.findIndex(category => category.name === categoryName)

  if (index !== -1)
    return [{ icon: categoryIcons[index].icon, color: categoryIcons[index].color }]

  return [{ icon: 'ri-question-line', color: 'primary' }]
}
<\/script>

<template>
  <div>
    <VCardText>
      <VRow>
        <VCol
          cols="12"
          offset-md="8"
          md="4"
        >
          <VTextField
            v-model="search"
            label="Search"
            placeholder="Search ..."
            append-inner-icon="ri-search-line"
            single-line
            hide-details
            dense
            outlined
          />
        </VCol>
      </VRow>
    </VCardText>

    <!-- 👉 Data Table  -->
    <VDataTable
      :headers="headers"
      :items="productList || []"
      :search="search"
      :items-per-page="5"
      class="text-no-wrap"
    >
      <!-- product -->
      <template #item.product.name="{ item }">
        <div class="d-flex align-center">
          <div>
            <VImg
              :src="item.product.image"
              height="40"
              width="40"
            />
          </div>
          <div class="d-flex flex-column ms-3">
            <span class="d-block font-weight-medium text-truncate text-high-emphasis">{{ item.product.name }}</span>
            <span class="text-xs">{{ item.product.brand }}</span>
          </div>
        </div>
      </template>

      <!-- category -->
      <template #item.product.category="{ item }">
        <div class="d-flex align-center">
          <VAvatar
            v-for="(category, index) in categoryIconFilter(item.product.category)"
            :key="index"
            size="26"
            :color="category.color"
            variant="tonal"
          >
            <VIcon
              size="18"
              :color="category.color"
              class="rounded-0"
            >
              {{ category.icon }}
            </VIcon>
          </VAvatar>
          <span class="ms-1 text-no-wrap">{{ item.product.category }}</span>
        </div>
      </template>

      <!-- buyer -->
      <template #item.buyer.name="{ item }">
        <div class="d-flex align-center">
          <VAvatar
            size="1.875rem"
            :color="!item.buyer.avatar ? 'primary' : undefined"
            :variant="!item.buyer.avatar ? 'tonal' : undefined"
          >
            <VImg
              v-if="item.buyer.avatar"
              :src="item.buyer.avatar"
            />
            <span
              v-else
              class="text-sm"
            >{{ item.buyer.name.slice(0, 2).toUpperCase() }}</span>
          </VAvatar>
          <span class="text-no-wrap font-weight-medium text-high-emphasis ms-2">{{ item.buyer.name }}</span>
        </div>
      </template>

      <!-- Payment -->
      <template #item.payment="{ item }">
        <div class="d-flex flex-column">
          <div class="d-flex align-center text-high-emphasis">
            <span>\${{ item.payment.paidAmount }}</span>
            <span v-if="item.payment.paidAmount !== item.payment.total">/{{ item.payment.total }}</span>
          </div>
          <span class="text-xs text-no-wrap">{{ item.payment.receivedPaymentStatus }}</span>
        </div>
      </template>

      <!-- Status -->
      <template #item.status="{ item }">
        <VChip
          :color="resolveStatusColor(item.payment.status)"
          :class="\`text-\${resolveStatusColor(item.payment.status)}\`"
          size="small"
          class="font-weight-medium"
        >
          {{ item.payment.status }}
        </VChip>
      </template>

      <!-- Delete -->
      <template #item.delete="{ item }">
        <IconBtn
          size="small"
          @click="deleteItem(item.product.id)"
        >
          <VIcon icon="ri-delete-bin-line" />
        </IconBtn>
      </template>
    </VDataTable>
  </div>
</template>
`,js:`<script setup>
const { data: productList } = await useApi('pages/datatable')
const search = ref('')

// headers
const headers = [
  {
    title: 'PRODUCT',
    key: 'product.name',
  },
  {
    title: 'DATE',
    key: 'date',
  },
  {
    title: 'CATEGORY',
    key: 'product.category',
  },
  {
    title: 'BUYERS',
    key: 'buyer.name',
  },
  {
    title: 'PAYMENT',
    key: 'payment',
    sortable: false,
  },
  {
    title: 'STATUS',
    key: 'status',
    sortable: false,
  },
  {
    title: 'DELETE',
    key: 'delete',
    sortable: false,
  },
]

const deleteItem = itemId => {
  if (!productList.value)
    return
  const index = productList.value.findIndex(item => item.product.id === itemId)

  productList.value.splice(index, 1)
}

const categoryIcons = [
  {
    name: 'Mouse',
    icon: 'ri-mouse-fill',
    color: 'warning',
  },
  {
    name: 'Glass',
    icon: 'ri-glasses-line',
    color: 'primary',
  },
  {
    name: 'Smart Watch',
    icon: 'ri-time-line',
    color: 'success',
  },
  {
    name: 'Bag',
    icon: 'ri-shopping-bag-line',
    color: 'info',
  },
  {
    name: 'Storage Device',
    icon: 'ri-tape-line',
    color: 'warning',
  },
  {
    name: 'Bluetooth',
    icon: 'ri-bluetooth-line',
    color: 'error',
  },
  {
    name: 'Gaming',
    icon: 'ri-gamepad-line',
    color: 'warning',
  },
  {
    name: 'Home',
    icon: 'ri-home-line',
    color: 'error',
  },
  {
    name: 'VR',
    icon: 'ri-goggles-line',
    color: 'primary',
  },
  {
    name: 'Shoes',
    icon: 'ri-omega',
    color: 'success',
  },
  {
    name: 'Electronics',
    icon: 'ri-flashlight-fill',
    color: 'info',
  },
  {
    name: 'Projector',
    icon: 'ri-projector-line',
    color: 'warning',
  },
  {
    name: 'iPod',
    icon: 'ri-music-line',
    color: 'error',
  },
  {
    name: 'Keyboard',
    icon: 'ri-keyboard-box-line',
    color: 'primary',
  },
  {
    name: 'Smart Phone',
    icon: 'ri-smartphone-line',
    color: 'success',
  },
  {
    name: 'Smart TV',
    icon: 'ri-tv-line',
    color: 'info',
  },
  {
    name: 'Google Home',
    icon: 'ri-google-line',
    color: 'warning',
  },
  {
    name: 'Mac',
    icon: 'ri-apple-line',
    color: 'error',
  },
  {
    name: 'Headphone',
    icon: 'ri-headphone-line',
    color: 'primary',
  },
  {
    name: 'iMac',
    icon: 'ri-computer-line',
    color: 'success',
  },
  {
    name: 'iPhone',
    icon: 'ri-apple-line',
    color: 'warning',
  },
]

const resolveStatusColor = status => {
  if (status === 'Confirmed')
    return 'primary'
  if (status === 'Completed')
    return 'success'
  if (status === 'Cancelled')
    return 'error'
}

const categoryIconFilter = categoryName => {
  const index = categoryIcons.findIndex(category => category.name === categoryName)
  if (index !== -1)
    return [{
      icon: categoryIcons[index].icon,
      color: categoryIcons[index].color,
    }]
  
  return [{
    icon: 'ri-question-line',
    color: 'primary',
  }]
}
<\/script>

<template>
  <div>
    <VCardText>
      <VRow>
        <VCol
          cols="12"
          offset-md="8"
          md="4"
        >
          <VTextField
            v-model="search"
            label="Search"
            placeholder="Search ..."
            append-inner-icon="ri-search-line"
            single-line
            hide-details
            dense
            outlined
          />
        </VCol>
      </VRow>
    </VCardText>

    <!-- 👉 Data Table  -->
    <VDataTable
      :headers="headers"
      :items="productList || []"
      :search="search"
      :items-per-page="5"
      class="text-no-wrap"
    >
      <!-- product -->
      <template #item.product.name="{ item }">
        <div class="d-flex align-center">
          <div>
            <VImg
              :src="item.product.image"
              height="40"
              width="40"
            />
          </div>
          <div class="d-flex flex-column ms-3">
            <span class="d-block font-weight-medium text-truncate text-high-emphasis">{{ item.product.name }}</span>
            <span class="text-xs">{{ item.product.brand }}</span>
          </div>
        </div>
      </template>

      <!-- category -->
      <template #item.product.category="{ item }">
        <div class="d-flex align-center">
          <VAvatar
            v-for="(category, index) in categoryIconFilter(item.product.category)"
            :key="index"
            size="26"
            :color="category.color"
            variant="tonal"
          >
            <VIcon
              size="18"
              :color="category.color"
              class="rounded-0"
            >
              {{ category.icon }}
            </VIcon>
          </VAvatar>
          <span class="ms-1 text-no-wrap">{{ item.product.category }}</span>
        </div>
      </template>

      <!-- buyer -->
      <template #item.buyer.name="{ item }">
        <div class="d-flex align-center">
          <VAvatar
            size="1.875rem"
            :color="!item.buyer.avatar ? 'primary' : undefined"
            :variant="!item.buyer.avatar ? 'tonal' : undefined"
          >
            <VImg
              v-if="item.buyer.avatar"
              :src="item.buyer.avatar"
            />
            <span
              v-else
              class="text-sm"
            >{{ item.buyer.name.slice(0, 2).toUpperCase() }}</span>
          </VAvatar>
          <span class="text-no-wrap font-weight-medium text-high-emphasis ms-2">{{ item.buyer.name }}</span>
        </div>
      </template>

      <!-- Payment -->
      <template #item.payment="{ item }">
        <div class="d-flex flex-column">
          <div class="d-flex align-center text-high-emphasis">
            <span>\${{ item.payment.paidAmount }}</span>
            <span v-if="item.payment.paidAmount !== item.payment.total">/{{ item.payment.total }}</span>
          </div>
          <span class="text-xs text-no-wrap">{{ item.payment.receivedPaymentStatus }}</span>
        </div>
      </template>

      <!-- Status -->
      <template #item.status="{ item }">
        <VChip
          :color="resolveStatusColor(item.payment.status)"
          :class="\`text-\${resolveStatusColor(item.payment.status)}\`"
          size="small"
          class="font-weight-medium"
        >
          {{ item.payment.status }}
        </VChip>
      </template>

      <!-- Delete -->
      <template #item.delete="{ item }">
        <IconBtn
          size="small"
          @click="deleteItem(item.product.id)"
        >
          <VIcon icon="ri-delete-bin-line" />
        </IconBtn>
      </template>
    </VDataTable>
  </div>
</template>
`},wa={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'
import type { Data } from '@db/pages/datatable/types'

const editDialog = ref(false)
const deleteDialog = ref(false)

const defaultItem = ref<Data>({
  responsiveId: '',
  id: -1,
  avatar: '',
  fullName: '',
  post: '',
  email: '',
  city: '',
  startDate: '',
  salary: -1,
  age: '',
  experience: '',
  status: -1,
})

const editedItem = ref<Data>(defaultItem.value)
const editedIndex = ref(-1)
const userList = ref<Data[]>([])

// status options
const selectedOptions = [
  { text: 'Current', value: 1 },
  { text: 'Professional', value: 2 },
  { text: 'Rejected', value: 3 },
  { text: 'Resigned', value: 4 },
  { text: 'Applied', value: 5 },
]

// headers
const headers = [
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'SALARY', key: 'salary' },
  { title: 'AGE', key: 'age' },
  { title: 'STATUS', key: 'status' },
  { title: 'ACTIONS', key: 'actions' },
]

const resolveStatusVariant = (status: number) => {
  if (status === 1)
    return { color: 'primary', text: 'Current' }
  else if (status === 2)
    return { color: 'success', text: 'Professional' }
  else if (status === 3)
    return { color: 'error', text: 'Rejected' }
  else if (status === 4)
    return { color: 'warning', text: 'Resigned' }
  else
    return { color: 'info', text: 'Applied' }
}

// 👉 methods
const editItem = (item: Data) => {
  editedIndex.value = userList.value.indexOf(item)
  editedItem.value = { ...item }
  editDialog.value = true
}

const deleteItem = (item: Data) => {
  editedIndex.value = userList.value.indexOf(item)
  editedItem.value = { ...item }
  deleteDialog.value = true
}

const close = () => {
  editDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
}

const closeDelete = () => {
  deleteDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
}

const save = () => {
  if (editedIndex.value > -1)
    Object.assign(userList.value[editedIndex.value], editedItem.value)

  else
    userList.value.push(editedItem.value)

  close()
}

const deleteItemConfirm = () => {
  userList.value.splice(editedIndex.value, 1)
  closeDelete()
}

onMounted(() => {
  userList.value = JSON.parse(JSON.stringify(data))
})
<\/script>

<template>
  <!-- 👉 Datatable  -->
  <VDataTable
    :headers="headers"
    :items="userList"
    :items-per-page="5"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <!-- avatar -->
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>

        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        density="comfortable"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>

    <!-- Actions -->
    <template #item.actions="{ item }">
      <div class="d-flex gap-1">
        <IconBtn
          size="small"
          @click="editItem(item)"
        >
          <VIcon icon="ri-pencil-line" />
        </IconBtn>
        <IconBtn
          size="small"
          @click="deleteItem(item)"
        >
          <VIcon icon="ri-delete-bin-line" />
        </IconBtn>
      </div>
    </template>
  </VDataTable>

  <!-- 👉 Edit Dialog  -->
  <VDialog
    v-model="editDialog"
    max-width="600px"
  >
    <VCard>
      <VCardTitle>
        <span class="headline">Edit Item</span>
      </VCardTitle>

      <VCardText>
        <VContainer>
          <VRow>
            <!-- fullName -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.fullName"
                label="User name"
              />
            </VCol>

            <!-- email -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.email"
                label="Email"
              />
            </VCol>

            <!-- salary -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.salary"
                label="Salary"
                prefix="$"
                type="number"
              />
            </VCol>

            <!-- age -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.age"
                label="Age"
                type="number"
              />
            </VCol>

            <!-- start date -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.startDate"
                label="Date"
              />
            </VCol>

            <!-- status -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VSelect
                v-model="editedItem.status"
                :items="selectedOptions"
                item-title="text"
                item-value="value"
                label="Status"
                variant="outlined"
              />
            </VCol>
          </VRow>
        </VContainer>
      </VCardText>

      <VCardActions>
        <VSpacer />

        <VBtn
          color="error"
          variant="outlined"
          @click="close"
        >
          Cancel
        </VBtn>

        <VBtn
          color="success"
          variant="elevated"
          @click="save"
        >
          Save
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- 👉 Delete Dialog  -->
  <VDialog
    v-model="deleteDialog"
    max-width="500px"
  >
    <VCard>
      <VCardTitle>
        Are you sure you want to delete this item?
      </VCardTitle>

      <VCardActions>
        <VSpacer />

        <VBtn
          color="error"
          variant="outlined"
          @click="closeDelete"
        >
          Cancel
        </VBtn>

        <VBtn
          color="success"
          variant="elevated"
          @click="deleteItemConfirm"
        >
          OK
        </VBtn>

        <VSpacer />
      </VCardActions>
    </VCard>
  </VDialog>
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

const editDialog = ref(false)
const deleteDialog = ref(false)

const defaultItem = ref({
  responsiveId: '',
  id: -1,
  avatar: '',
  fullName: '',
  post: '',
  email: '',
  city: '',
  startDate: '',
  salary: -1,
  age: '',
  experience: '',
  status: -1,
})

const editedItem = ref(defaultItem.value)
const editedIndex = ref(-1)
const userList = ref([])

// status options
const selectedOptions = [
  {
    text: 'Current',
    value: 1,
  },
  {
    text: 'Professional',
    value: 2,
  },
  {
    text: 'Rejected',
    value: 3,
  },
  {
    text: 'Resigned',
    value: 4,
  },
  {
    text: 'Applied',
    value: 5,
  },
]

// headers
const headers = [
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'SALARY',
    key: 'salary',
  },
  {
    title: 'AGE',
    key: 'age',
  },
  {
    title: 'STATUS',
    key: 'status',
  },
  {
    title: 'ACTIONS',
    key: 'actions',
  },
]

const resolveStatusVariant = status => {
  if (status === 1)
    return {
      color: 'primary',
      text: 'Current',
    }
  else if (status === 2)
    return {
      color: 'success',
      text: 'Professional',
    }
  else if (status === 3)
    return {
      color: 'error',
      text: 'Rejected',
    }
  else if (status === 4)
    return {
      color: 'warning',
      text: 'Resigned',
    }
  else
    return {
      color: 'info',
      text: 'Applied',
    }
}

const editItem = item => {
  editedIndex.value = userList.value.indexOf(item)
  editedItem.value = { ...item }
  editDialog.value = true
}

const deleteItem = item => {
  editedIndex.value = userList.value.indexOf(item)
  editedItem.value = { ...item }
  deleteDialog.value = true
}

const close = () => {
  editDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
}

const closeDelete = () => {
  deleteDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
}

const save = () => {
  if (editedIndex.value > -1)
    Object.assign(userList.value[editedIndex.value], editedItem.value)
  else
    userList.value.push(editedItem.value)
  close()
}

const deleteItemConfirm = () => {
  userList.value.splice(editedIndex.value, 1)
  closeDelete()
}

onMounted(() => {
  userList.value = JSON.parse(JSON.stringify(data))
})
<\/script>

<template>
  <!-- 👉 Datatable  -->
  <VDataTable
    :headers="headers"
    :items="userList"
    :items-per-page="5"
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <!-- avatar -->
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>

        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        density="comfortable"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>

    <!-- Actions -->
    <template #item.actions="{ item }">
      <div class="d-flex gap-1">
        <IconBtn
          size="small"
          @click="editItem(item)"
        >
          <VIcon icon="ri-pencil-line" />
        </IconBtn>
        <IconBtn
          size="small"
          @click="deleteItem(item)"
        >
          <VIcon icon="ri-delete-bin-line" />
        </IconBtn>
      </div>
    </template>
  </VDataTable>

  <!-- 👉 Edit Dialog  -->
  <VDialog
    v-model="editDialog"
    max-width="600px"
  >
    <VCard>
      <VCardTitle>
        <span class="headline">Edit Item</span>
      </VCardTitle>

      <VCardText>
        <VContainer>
          <VRow>
            <!-- fullName -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.fullName"
                label="User name"
              />
            </VCol>

            <!-- email -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.email"
                label="Email"
              />
            </VCol>

            <!-- salary -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.salary"
                label="Salary"
                prefix="$"
                type="number"
              />
            </VCol>

            <!-- age -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.age"
                label="Age"
                type="number"
              />
            </VCol>

            <!-- start date -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VTextField
                v-model="editedItem.startDate"
                label="Date"
              />
            </VCol>

            <!-- status -->
            <VCol
              cols="12"
              sm="6"
              md="4"
            >
              <VSelect
                v-model="editedItem.status"
                :items="selectedOptions"
                item-title="text"
                item-value="value"
                label="Status"
                variant="outlined"
              />
            </VCol>
          </VRow>
        </VContainer>
      </VCardText>

      <VCardActions>
        <VSpacer />

        <VBtn
          color="error"
          variant="outlined"
          @click="close"
        >
          Cancel
        </VBtn>

        <VBtn
          color="success"
          variant="elevated"
          @click="save"
        >
          Save
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- 👉 Delete Dialog  -->
  <VDialog
    v-model="deleteDialog"
    max-width="500px"
  >
    <VCard>
      <VCardTitle>
        Are you sure you want to delete this item?
      </VCardTitle>

      <VCardActions>
        <VSpacer />

        <VBtn
          color="error"
          variant="outlined"
          @click="closeDelete"
        >
          Cancel
        </VBtn>

        <VBtn
          color="success"
          variant="elevated"
          @click="deleteItemConfirm"
        >
          OK
        </VBtn>

        <VSpacer />
      </VCardActions>
    </VCard>
  </VDialog>
</template>
`},Ta={ts:`<script setup lang="ts">
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  { title: 'NAME', key: 'fullName' },
  { title: 'EMAIL', key: 'email' },
  { title: 'DATE', key: 'startDate' },
  { title: 'SALARY', key: 'salary' },
  { title: 'AGE', key: 'age' },
  { title: 'STATUS', key: 'status' },
]

const resolveStatusVariant = (status: number) => {
  if (status === 1)
    return { color: 'primary', text: 'Current' }
  else if (status === 2)
    return { color: 'success', text: 'Professional' }
  else if (status === 3)
    return { color: 'error', text: 'Rejected' }
  else if (status === 4)
    return { color: 'warning', text: 'Resigned' }
  else
    return { color: 'info', text: 'Applied' }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    show-select
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`,js:`<script setup>
import data from '@/views/demos/forms/tables/data-table/datatable'

const headers = [
  {
    title: 'NAME',
    key: 'fullName',
  },
  {
    title: 'EMAIL',
    key: 'email',
  },
  {
    title: 'DATE',
    key: 'startDate',
  },
  {
    title: 'SALARY',
    key: 'salary',
  },
  {
    title: 'AGE',
    key: 'age',
  },
  {
    title: 'STATUS',
    key: 'status',
  },
]

const resolveStatusVariant = status => {
  if (status === 1)
    return {
      color: 'primary',
      text: 'Current',
    }
  else if (status === 2)
    return {
      color: 'success',
      text: 'Professional',
    }
  else if (status === 3)
    return {
      color: 'error',
      text: 'Rejected',
    }
  else if (status === 4)
    return {
      color: 'warning',
      text: 'Resigned',
    }
  else
    return {
      color: 'info',
      text: 'Applied',
    }
}
<\/script>

<template>
  <VDataTable
    :headers="headers"
    :items="data"
    :items-per-page="5"
    show-select
    class="text-no-wrap"
  >
    <!-- full name -->
    <template #item.fullName="{ item }">
      <div class="d-flex align-center">
        <VAvatar
          size="32"
          :color="item.avatar ? '' : 'primary'"
          :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
          :variant="!item.avatar ? 'tonal' : undefined"
        >
          <VImg
            v-if="item.avatar"
            :src="item.avatar"
          />
          <span
            v-else
            class="text-sm"
          >{{ avatarText(item.fullName) }}</span>
        </VAvatar>
        <div class="d-flex flex-column ms-3">
          <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.fullName }}</span>
          <small>{{ item.post }}</small>
        </div>
      </div>
    </template>

    <!-- status -->
    <template #item.status="{ item }">
      <VChip
        :color="resolveStatusVariant(item.status).color"
        class="font-weight-medium"
        size="small"
      >
        {{ resolveStatusVariant(item.status).text }}
      </VChip>
    </template>
  </VDataTable>
</template>
`},Qa={__name:"data-table",setup(I){return(m,n)=>{const r=Da,i=ge,t=xa,p=fa,v=ua,g=oa,E=ta,D=Ke,k=$e,N=Pe,c=we;return u(),y(q,null,{default:a(()=>[e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Basic",code:ba,"no-padding":""},{default:a(()=>[e(r)]),_:1},8,["code"])]),_:1}),e(x,null,{default:a(()=>[e(i,{title:"Dense",code:ka,"no-padding":""},{default:a(()=>[e(t)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Cell Slot",code:Va,"no-padding":""},{default:a(()=>[e(p)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Row Selection",code:Ta,"no-padding":""},{default:a(()=>[e(v)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Fixed Header",code:Na,"no-padding":""},{default:a(()=>[e(g)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Expandable Rows",code:Aa,"no-padding":""},{default:a(()=>[e(E)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Grouping Rows",code:Sa,"no-padding":""},{default:a(()=>[e(D)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Row Editing via Dialog",code:wa,"no-padding":""},{default:a(()=>[e(k)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"External Pagination",code:Ia,"no-padding":""},{default:a(()=>[e(N)]),_:1},8,["code"])]),_:1}),e(x,{cols:"12"},{default:a(()=>[e(i,{title:"Kitchen Sink",code:Ca,"no-padding":""},{default:a(()=>[e(c)]),_:1},8,["code"])]),_:1})]),_:1})}}};export{Qa as default};
