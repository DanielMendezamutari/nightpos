import{_ as ne}from"./CustomRadiosWithIcon-DvFhDWKo.js";import{_ as re,r,o as _,g as k,w as l,b as c,a as e,e as v,f as x,j as h,h as o,d as a,ab as B,ac as T,n as L,p as me,c as q,F as ee,y as ae,q as ie,L as M,Z as le,$ as oe,A as ue,B as ce,a0 as I,aq as Ve,x as i,t as pe,m as fe}from"./index-JbOKxrHA.js";import{c as be,V as te}from"./VCard-mZRwb7Bc.js";import{V as Ce}from"./VSpacer-CqUb_kiK.js";import{V as X}from"./VCardText-BrGrXzh1.js";import{V as g}from"./VForm-mq_8USzW.js";import{V as m}from"./VTextField-8AyoFLvA.js";import{V as se}from"./VTextarea-D8byTBHB.js";import{V as D}from"./VSelect-DuAukU_3.js";import{V as R}from"./VCheckbox-CynVk4q_.js";import{a as G,b as O,c as K,V as ve}from"./VExpansionPanel-vV4j1ht4.js";import{_ as he}from"./AppDateTimePicker-Cn8dustR.js";import{a as Q,V as ye}from"./VTabs-C24NeLsE.js";import{V as we,a as Z}from"./VWindowItem-CALYkpxQ.js";import{r as z,e as xe}from"./validators-DD7Euqo8.js";import{V as _e}from"./VAutocomplete-OzRbKRRn.js";import{_ as Fe}from"./AppCardCode-DMS4geAP.js";/* empty css                   */import"./VCounter-DdHJYSnk.js";import"./VField-DynU_wfW.js";import"./VCheckboxBtn-BDQekAcb.js";import"./helpers-cDhjtOJI.js";import"./filter-C2Tglutt.js";import"./_commonjsHelpers-BosuxZz1.js";const N=F=>(ue("data-v-902bdf59"),F=F(),ce(),F),ge={class:"w-100 sticky-header overflow-hidden rounded-t"},ke={class:"d-flex align-center gap-4 flex-wrap bg-custom-background pa-6"},Te=N(()=>c("h2",{class:"text-lg font-weight-medium mb-6"}," 1. Delivery Address ",-1)),Re=N(()=>c("p",{class:"text-high-emphasis text-base mb-1"}," Address Type ",-1)),Ne=N(()=>c("h2",{class:"text-lg font-weight-medium mb-6"}," 2. Delivery Type ",-1)),Le=N(()=>c("h2",{class:"text-lg font-weight-medium mb-6"}," 3. Apply Promo code ",-1)),Ie={class:"d-flex align-center gap-4"},Ue={class:"d-flex align-center gap-2 my-4"},Pe=N(()=>c("span",null,"OR",-1)),Se=N(()=>c("h2",{class:"text-lg font-weight-medium mb-6"}," 4. Payment Method ",-1)),Be=N(()=>c("p",null," Cash on delivery is a mode of payment where you make the payment after the goods/services are received. ",-1)),De=N(()=>c("p",null,"You can pay cash or make the payment via debit/credit card directly to the delivery person.",-1)),Ae=[Be,De],Ee={__name:"DemoFormLayoutFormSticky",setup(F){const f=[{title:"Standard",desc:"Delivery in 3-5 days.",value:"standard",icon:"ri-briefcase-line"},{title:"Express",desc:"Delivery within 2 days.",value:"express",icon:"ri-rocket-line"},{title:"Overnight",desc:"Delivery within a days.",value:"overnight",icon:"ri-vip-crown-line"}],b=[{code:"TAKEITALL",desc:"Apply this code to get 15% discount on orders above 20$."},{code:"FESTIVE10",desc:"Apply this code to get 10% discount on all orders."},{code:"MYSTERYDEAL",desc:"Apply this code to get discount between 10% - 50%."}],d=r({fullName:"",email:"",contactNumber:null,altContactNumber:null,address:"",pincode:null,Landmark:"",city:"",state:null,defaultAddress:!1,addressType:"home",deliveryType:"standard",promoCode:"",paymentMethod:"card",cardNumber:null,cardName:"",cardExDate:"",cardCvv:""});return(V,n)=>{const w=ne;return _(),k(te,{class:"overflow-visible"},{default:l(()=>[c("div",ge,[c("div",ke,[e(be,null,{default:l(()=>[v("Sticky Action Bar")]),_:1}),e(Ce),c("div",null,[e(x,{variant:"tonal",class:"me-4"},{default:l(()=>[v(" Back ")]),_:1}),e(x,null,{default:l(()=>[v("Place Order")]),_:1})])])]),e(X,null,{default:l(()=>[e(h,null,{default:l(()=>[e(o,{md:"8",cols:"12",class:"mx-auto"},{default:l(()=>[e(g,null,{default:l(()=>[Te,e(h,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d).fullName,"onUpdate:modelValue":n[0]||(n[0]=t=>a(d).fullName=t),label:"Full Name",placeholder:"John Doe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d).email,"onUpdate:modelValue":n[1]||(n[1]=t=>a(d).email=t),label:"Email",placeholder:"john.doe",suffix:"@example.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d).contactNumber,"onUpdate:modelValue":n[2]||(n[2]=t=>a(d).contactNumber=t),label:"Contact Number",placeholder:"658 123 4567",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d).altContactNumber,"onUpdate:modelValue":n[3]||(n[3]=t=>a(d).altContactNumber=t),label:"Alternate Number",placeholder:"658 123 4567",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(se,{modelValue:a(d).address,"onUpdate:modelValue":n[4]||(n[4]=t=>a(d).address=t),label:"Address",placeholder:"1456, Mall Road",rows:"2"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d).pincode,"onUpdate:modelValue":n[5]||(n[5]=t=>a(d).pincode=t),label:"Pincode",placeholder:"658468",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d).Landmark,"onUpdate:modelValue":n[6]||(n[6]=t=>a(d).Landmark=t),label:"Landmark",placeholder:"Nr. Wall Street"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d).city,"onUpdate:modelValue":n[7]||(n[7]=t=>a(d).city=t),label:"City",placeholder:"Jackson"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(D,{modelValue:a(d).state,"onUpdate:modelValue":n[8]||(n[8]=t=>a(d).state=t),label:"State",placeholder:"California",items:["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","Florida"]},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(R,{modelValue:a(d).defaultAddress,"onUpdate:modelValue":n[9]||(n[9]=t=>a(d).defaultAddress=t),label:"Use this as default delivery address"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[Re,e(B,{modelValue:a(d).addressType,"onUpdate:modelValue":n[10]||(n[10]=t=>a(d).addressType=t),inline:""},{default:l(()=>[e(T,{label:"Home (All day delivery)",value:"home"}),e(T,{label:"Office (Delivery between 10 AM - 5 PM)",value:"work"})]),_:1},8,["modelValue"])]),_:1})]),_:1}),e(L,{class:"my-4"}),Ne,e(w,{"selected-radio":a(d).deliveryType,"onUpdate:selectedRadio":n[11]||(n[11]=t=>a(d).deliveryType=t),"radio-content":f,"grid-column":{sm:"4",cols:"12"}},null,8,["selected-radio"]),e(L,{class:"my-4"}),Le,c("div",Ie,[e(m,{modelValue:a(d).promoCode,"onUpdate:modelValue":n[12]||(n[12]=t=>a(d).promoCode=t),density:"compact",placeholder:"TAKEITALL"},null,8,["modelValue"]),e(x,null,{default:l(()=>[v("Apply")]),_:1})]),c("div",Ue,[e(L,{style:{"border-style":"dashed"}}),Pe,e(L,{style:{"border-style":"dashed"}})]),e(me,{class:"border rounded py-0",lines:"two"},{default:l(()=>[(_(),q(ee,null,ae(b,(t,s)=>e(ie,{key:t.code,title:t.code,subtitle:t.desc,class:M(s!==0?"border-t":"")},{append:l(()=>[e(x,{variant:"outlined"},{default:l(()=>[v(" Apply ")]),_:1})]),_:2},1032,["title","subtitle","class"])),64))]),_:1}),e(L,{class:"my-4"}),Se,e(B,{modelValue:a(d).paymentMethod,"onUpdate:modelValue":n[13]||(n[13]=t=>a(d).paymentMethod=t),inline:"",class:"mb-4"},{default:l(()=>[e(T,{value:"card",label:"Credit/Debit/ATM Card"}),e(T,{value:"cash-on-delivery",label:"Cash On Delivery"})]),_:1},8,["modelValue"]),le(e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(m,{label:"Card Number",placeholder:"1356 3215 6548 7898"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{label:"Name",placeholder:"John Doe"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(m,{label:"Exp. Date",placeholder:"MM/YY"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(m,{label:"CVV Code",placeholder:"654"})]),_:1})]),_:1},512),[[oe,a(d).paymentMethod==="card"]]),le(c("div",null,Ae,512),[[oe,a(d).paymentMethod==="cash-on-delivery"]])]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})}}},ze=re(Ee,[["__scopeId","data-v-902bdf59"]]),Me=c("div",{class:"w-100"},[c("div",{class:"d-flex justify-space-between"},[c("h6",{class:"text-base font-weight-medium"}," Standard 3-5 Days "),c("span",{class:"text-sm text-disabled"}," Free ")]),c("div",{class:"text-sm text-medium-emphasis"}," Friday, 15 Nov - Monday, 18 Nov ")],-1),qe=c("div",{class:"w-100"},[c("div",{class:"d-flex justify-space-between"},[c("h5",{class:"text-base font-weight-medium"}," Express "),c("span",{class:"text-sm text-disabled"}," $5.00 ")]),c("div",{class:"text-sm text-medium-emphasis"}," Friday, 15 Nov - Sunday, 17 Nov ")],-1),$e=c("div",{class:"w-100"},[c("div",{class:"d-flex justify-space-between"},[c("h6",{class:"text-base font-weight-medium"}," Overnight "),c("span",{class:"text-sm text-disabled"}," $10.00 ")]),c("div",{class:"text-sm text-medium-emphasis"}," Friday, 15 Nov - Saturday, 16 Nov ")],-1),je={class:"me-1"},He=c("div",{class:"text-high-emphasis"}," Cash on Delivery is a type of payment method where the recipient make payment for the order at the time of delivery rather than in advance. ",-1),Je={class:"d-flex gap-4"},We={__name:"DemoFormLayoutCollapsible",setup(F){const f=["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","Florida","Georgia","Hawaii"],b=r("home"),d=r("standard"),V=r("credit-debit-card"),n=r(0),w=[{radioValue:"credit-debit-card",radioLabel:"Credit/Debit/ATM Card",icon:"ri-bank-card-line"},{radioValue:"cash-on-delivery",radioLabel:"Cash On Delivery",icon:"ri-question-line"}];return(t,s)=>(_(),k(ve,{modelValue:a(n),"onUpdate:modelValue":s[6]||(s[6]=u=>i(n)?n.value=u:null)},{default:l(()=>[e(G,null,{default:l(()=>[e(O,null,{default:l(()=>[v("Delivery Address")]),_:1}),e(K,null,{default:l(()=>[e(g,{class:"pt-4 pb-2",onSubmit:I(()=>{},["prevent"])},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{label:"Full Name",placeholder:"John Doe"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{label:"Phone No",type:"number",placeholder:"+1 123 456 7890"})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(se,{label:"Address",placeholder:"1234 Main St, New York, NY 10001, USA",rows:"3"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{label:"Pincode",placeholder:"123456",type:"number"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{label:"Landmark",placeholder:"Near City Mall"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{label:"City",placeholder:"New York"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(D,{items:f,label:"State",placeholder:"Select State"})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(Ve,{class:"mb-3"},{default:l(()=>[v(" Address Type ")]),_:1}),e(B,{modelValue:a(b),"onUpdate:modelValue":s[0]||(s[0]=u=>i(b)?b.value=u:null),inline:""},{default:l(()=>[c("div",null,[e(T,{label:"Home (All day delivery)",value:"home"}),e(T,{label:"Office (Delivery between 10 AM - 5 PM)",value:"office"})])]),_:1},8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1})]),_:1}),e(G,null,{default:l(()=>[e(O,null,{default:l(()=>[v("Delivery Options")]),_:1}),e(K,null,{default:l(()=>[e(B,{modelValue:a(d),"onUpdate:modelValue":s[4]||(s[4]=u=>i(d)?d.value=u:null),class:"delivery-options pt-4 pb-2"},{default:l(()=>[c("div",{class:M(["delivery-option d-flex rounded-t",a(d)==="standard"?"active":""]),onClick:s[1]||(s[1]=u=>d.value="standard")},[e(T,{inline:"",value:"standard",class:"mt-n4"}),Me],2),c("div",{class:M(["delivery-option d-flex",a(d)==="express"?"active":""]),onClick:s[2]||(s[2]=u=>d.value="express")},[e(T,{inline:"",class:"mt-n4",value:"express"}),qe],2),c("div",{class:M(["delivery-option d-flex rounded-b",a(d)==="overnight"?"active":""]),onClick:s[3]||(s[3]=u=>d.value="overnight")},[e(T,{inline:"",class:"mt-n4",value:"overnight"}),$e],2)]),_:1},8,["modelValue"])]),_:1})]),_:1}),e(G,null,{default:l(()=>[e(O,null,{default:l(()=>[v("Payment Method")]),_:1}),e(K,null,{default:l(()=>[e(h,null,{default:l(()=>[e(o,{md:"6",cols:"12"},{default:l(()=>[e(g,{class:"pt-4 pb-2"},{default:l(()=>[c("div",null,[e(B,{modelValue:a(V),"onUpdate:modelValue":s[5]||(s[5]=u=>i(V)?V.value=u:null),inline:""},{default:l(()=>[c("div",null,[(_(),q(ee,null,ae(w,u=>e(T,{key:u.radioValue,value:u.radioValue},{label:l(()=>[c("span",je,pe(u.radioLabel),1),e(fe,{size:"18",icon:u.icon},null,8,["icon"])]),_:2},1032,["value"])),64))])]),_:1},8,["modelValue"])]),a(V)==="credit-debit-card"?(_(),k(h,{key:0},{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(m,{label:"Card Number",type:"number",placeholder:"1234 5678 9012 3456"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{label:"Name",placeholder:"john doe"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(m,{label:"Expiry Date",placeholder:"MM/YY"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(m,{label:"CVV Code",type:"number",max:"3",placeholder:"123"})]),_:1})]),_:1})):(_(),k(h,{key:1},{default:l(()=>[e(o,null,{default:l(()=>[He]),_:1})]),_:1}))]),_:1})]),_:1})]),_:1}),e(L,{class:"my-5"}),c("div",Je,[e(x,null,{default:l(()=>[v("Place Order")]),_:1}),e(x,{color:"secondary",variant:"tonal"},{default:l(()=>[v(" Cancel ")]),_:1})])]),_:1})]),_:1})]),_:1},8,["modelValue"]))}},Ye={__name:"DemoFormLayoutFormWithTabs",setup(F){const f=r("personal-info"),b=r(""),d=r(""),V=r(),n=r(""),w=r(),t=["USA","Canada","UK","Denmark","Germany","Iceland","Israel","Mexico"],s=["English","German","French","Spanish","Portuguese","Russian","Korean"],u=r(""),y=r(""),U=r(""),P=r(""),S=r(""),$=r(""),j=r(""),H=r(""),J=r(""),W=r(""),Y=r([]),A=r(!1),E=r(!1);return(hl,p)=>{const de=he;return _(),q(ee,null,[e(ye,{modelValue:a(f),"onUpdate:modelValue":p[0]||(p[0]=C=>i(f)?f.value=C:null)},{default:l(()=>[e(Q,{value:"personal-info"},{default:l(()=>[v(" Personal Info ")]),_:1}),e(Q,{value:"account-details"},{default:l(()=>[v(" Account Details ")]),_:1}),e(Q,{value:"social-links"},{default:l(()=>[v(" Social Links ")]),_:1})]),_:1},8,["modelValue"]),e(te,{flat:""},{default:l(()=>[e(X,null,{default:l(()=>[e(we,{modelValue:a(f),"onUpdate:modelValue":p[19]||(p[19]=C=>i(f)?f.value=C:null),class:"disable-tab-transition"},{default:l(()=>[e(Z,{value:"personal-info"},{default:l(()=>[e(g,{class:"mt-2"},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{md:"6",cols:"12"},{default:l(()=>[e(m,{modelValue:a(b),"onUpdate:modelValue":p[1]||(p[1]=C=>i(b)?b.value=C:null),label:"First name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{md:"6",cols:"12"},{default:l(()=>[e(m,{modelValue:a(d),"onUpdate:modelValue":p[2]||(p[2]=C=>i(d)?d.value=C:null),label:"Last name",placeholder:"Doe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(D,{modelValue:a(V),"onUpdate:modelValue":p[3]||(p[3]=C=>i(V)?V.value=C:null),items:t,label:"Country",placeholder:"Select Country"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(D,{modelValue:a(Y),"onUpdate:modelValue":p[4]||(p[4]=C=>i(Y)?Y.value=C:null),items:s,multiple:"",chips:"",clearable:"",label:"Language",placeholder:"Select Language"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(de,{modelValue:a(n),"onUpdate:modelValue":p[5]||(p[5]=C=>i(n)?n.value=C:null),label:"Birth Date",placeholder:"Select Birth Date"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(w),"onUpdate:modelValue":p[6]||(p[6]=C=>i(w)?w.value=C:null),type:"number",label:"Phone No.",placeholder:"+1 123 456 7890"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1}),e(Z,{value:"account-details"},{default:l(()=>[e(g,{class:"mt-2"},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(u),"onUpdate:modelValue":p[7]||(p[7]=C=>i(u)?u.value=C:null),label:"Username",placeholder:"Johndoe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(y),"onUpdate:modelValue":p[8]||(p[8]=C=>i(y)?y.value=C:null),label:"Email",suffix:"@example.com",placeholder:"johndoe@email.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(U),"onUpdate:modelValue":p[9]||(p[9]=C=>i(U)?U.value=C:null),label:"Password",placeholder:"············",type:a(A)?"text":"password","append-inner-icon":a(A)?"ri-eye-off-line":"ri-eye-line","onClick:appendInner":p[10]||(p[10]=C=>A.value=!a(A))},null,8,["modelValue","type","append-inner-icon"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(P),"onUpdate:modelValue":p[11]||(p[11]=C=>i(P)?P.value=C:null),label:"Confirm Password",placeholder:"············",type:a(E)?"text":"password","append-inner-icon":a(E)?"ri-eye-off-line":"ri-eye-line","onClick:appendInner":p[12]||(p[12]=C=>E.value=!a(E))},null,8,["modelValue","type","append-inner-icon"])]),_:1})]),_:1})]),_:1})]),_:1}),e(Z,{value:"social-links"},{default:l(()=>[e(g,{class:"mt-2"},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(S),"onUpdate:modelValue":p[13]||(p[13]=C=>i(S)?S.value=C:null),label:"Twitter",placeholder:"https://twitter.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a($),"onUpdate:modelValue":p[14]||(p[14]=C=>i($)?$.value=C:null),label:"Facebook",placeholder:"https://facebook.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(j),"onUpdate:modelValue":p[15]||(p[15]=C=>i(j)?j.value=C:null),label:"Google+",placeholder:"https://plus.google.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(H),"onUpdate:modelValue":p[16]||(p[16]=C=>i(H)?H.value=C:null),label:"LinkedIn",placeholder:"https://linkedin.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(J),"onUpdate:modelValue":p[17]||(p[17]=C=>i(J)?J.value=C:null),label:"Instagram",placeholder:"https://instagram.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(W),"onUpdate:modelValue":p[18]||(p[18]=C=>i(W)?W.value=C:null),label:"Quora",placeholder:"https://quora.com/username"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"])]),_:1}),e(L),e(X,{class:"d-flex gap-4"},{default:l(()=>[e(x,null,{default:l(()=>[v("Submit")]),_:1}),e(x,{color:"secondary",variant:"tonal"},{default:l(()=>[v(" Cancel ")]),_:1})]),_:1})]),_:1})],64)}}},Ge={__name:"DemoFormLayoutFormValidation",setup(F){const f=r(""),b=r(""),d=["Item 1","Item 2","Item 3","Item 4"],V=r(),n=r(!1),w=r();return(t,s)=>(_(),k(g,{ref_key:"form",ref:w,"lazy-validation":""},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(f),"onUpdate:modelValue":s[0]||(s[0]=u=>i(f)?f.value=u:null),rules:["requiredValidator"in t?t.requiredValidator:a(z)],label:"Name",placeholder:"John Doe",required:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(b),"onUpdate:modelValue":s[1]||(s[1]=u=>i(b)?b.value=u:null),rules:["emailValidator"in t?t.emailValidator:a(xe),"requiredValidator"in t?t.requiredValidator:a(z)],label:"E-mail",placeholder:"johndoe@email.com",required:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(D,{modelValue:a(V),"onUpdate:modelValue":s[2]||(s[2]=u=>i(V)?V.value=u:null),items:d,rules:["requiredValidator"in t?t.requiredValidator:a(z)],placeholder:"Select an Item",label:"Item",name:"select",require:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(R,{modelValue:a(n),"onUpdate:modelValue":s[3]||(s[3]=u=>i(n)?n.value=u:null),rules:["requiredValidator"in t?t.requiredValidator:a(z)],label:"Do you agree?",required:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12",class:"d-flex flex-wrap gap-4"},{default:l(()=>[e(x,{color:"success",onClick:s[4]||(s[4]=u=>{var y;return(y=a(w))==null?void 0:y.validate()})},{default:l(()=>[v(" Validate ")]),_:1}),e(x,{color:"error",onClick:s[5]||(s[5]=u=>{var y;return(y=a(w))==null?void 0:y.reset()})},{default:l(()=>[v(" Reset Form ")]),_:1}),e(x,{color:"warning",onClick:s[6]||(s[6]=u=>{var y;return(y=a(w))==null?void 0:y.resetValidation()})},{default:l(()=>[v(" Reset Validation ")]),_:1})]),_:1})]),_:1})]),_:1},512))}},Oe={__name:"DemoFormLayoutFormHint",setup(F){const f=r(""),b=r(""),d=r(),V=r(!1),n=["foo","bar","fizz","buzz"],w=r([]);return(t,s)=>(_(),k(g,{onSubmit:I(()=>{},["prevent"])},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(f),"onUpdate:modelValue":s[0]||(s[0]=u=>i(f)?f.value=u:null),label:"Username",placeholder:"Johndoe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(b),"onUpdate:modelValue":s[1]||(s[1]=u=>i(b)?b.value=u:null),label:"Email",type:"email",placeholder:"johndoe@email.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(d),"onUpdate:modelValue":s[2]||(s[2]=u=>i(d)?d.value=u:null),label:"Password",autocomplete:"on",type:"password","persistent-hint":"",placeholder:"············",hint:"Your password must be 8-20 characters long."},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(_e,{modelValue:a(w),"onUpdate:modelValue":s[3]||(s[3]=u=>i(w)?w.value=u:null),items:n,chips:"",multiple:"",label:"Autocomplete",placeholder:"Select"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(R,{modelValue:a(V),"onUpdate:modelValue":s[4]||(s[4]=u=>i(V)?V.value=u:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",class:"d-flex gap-4"},{default:l(()=>[e(x,{type:"submit"},{default:l(()=>[v(" Submit ")]),_:1}),e(x,{color:"secondary",type:"reset",variant:"tonal"},{default:l(()=>[v(" Reset ")]),_:1})]),_:1})]),_:1})]),_:1}))}},Ke={__name:"DemoFormLayoutMultipleColumn",setup(F){const f=r(""),b=r(""),d=r(""),V=r(""),n=r(""),w=r(""),t=r(!1);return(s,u)=>(_(),k(g,{onSubmit:I(()=>{},["prevent"])},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(f),"onUpdate:modelValue":u[0]||(u[0]=y=>i(f)?f.value=y:null),label:"First Name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(b),"onUpdate:modelValue":u[1]||(u[1]=y=>i(b)?b.value=y:null),label:"Last Name",placeholder:"Doe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(w),"onUpdate:modelValue":u[2]||(u[2]=y=>i(w)?w.value=y:null),label:"Email",placeholder:"johndoe@email.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(d),"onUpdate:modelValue":u[3]||(u[3]=y=>i(d)?d.value=y:null),label:"City",placeholder:"New York"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(V),"onUpdate:modelValue":u[4]||(u[4]=y=>i(V)?V.value=y:null),label:"Country",placeholder:"United States"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(m,{modelValue:a(n),"onUpdate:modelValue":u[5]||(u[5]=y=>i(n)?n.value=y:null),label:"Company",placeholder:"Pixinvent"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(R,{modelValue:a(t),"onUpdate:modelValue":u[6]||(u[6]=y=>i(t)?t.value=y:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",class:"d-flex gap-4"},{default:l(()=>[e(x,{type:"submit"},{default:l(()=>[v(" Submit ")]),_:1}),e(x,{type:"reset",color:"secondary",variant:"tonal"},{default:l(()=>[v(" Reset ")]),_:1})]),_:1})]),_:1})]),_:1}))}},Qe={__name:"DemoFormLayoutVerticalFormWithIcons",setup(F){const f=r(""),b=r(""),d=r(""),V=r(),n=r(!1);return(w,t)=>(_(),k(g,{onSubmit:t[5]||(t[5]=I(()=>{},["prevent"]))},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(f),"onUpdate:modelValue":t[0]||(t[0]=s=>i(f)?f.value=s:null),"prepend-inner-icon":"ri-user-line",label:"First Name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(b),"onUpdate:modelValue":t[1]||(t[1]=s=>i(b)?b.value=s:null),"prepend-inner-icon":"ri-mail-line",label:"Email",type:"email",placeholder:"johndoe@example.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(d),"onUpdate:modelValue":t[2]||(t[2]=s=>i(d)?d.value=s:null),"prepend-inner-icon":"ri-smartphone-line",label:"Mobile",placeholder:"+1 123 456 7890"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(V),"onUpdate:modelValue":t[3]||(t[3]=s=>i(V)?V.value=s:null),"prepend-inner-icon":"ri-lock-line",label:"Password",autocomplete:"on",type:"password",placeholder:"············"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(R,{modelValue:a(n),"onUpdate:modelValue":t[4]||(t[4]=s=>i(n)?n.value=s:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{type:"submit",class:"me-4"},{default:l(()=>[v(" Submit ")]),_:1}),e(x,{color:"secondary",type:"reset",variant:"tonal"},{default:l(()=>[v(" Reset ")]),_:1})]),_:1})]),_:1})]),_:1}))}},Ze={__name:"DemoFormLayoutVerticalForm",setup(F){const f=r(""),b=r(""),d=r(""),V=r(),n=r(!1);return(w,t)=>(_(),k(g,{onSubmit:I(()=>{},["prevent"])},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(f),"onUpdate:modelValue":t[0]||(t[0]=s=>i(f)?f.value=s:null),label:"First Name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(b),"onUpdate:modelValue":t[1]||(t[1]=s=>i(b)?b.value=s:null),label:"Email",type:"email",placeholder:"johndoe@example.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(d),"onUpdate:modelValue":t[2]||(t[2]=s=>i(d)?d.value=s:null),label:"Mobile",placeholder:"+1 123 456 7890",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(m,{modelValue:a(V),"onUpdate:modelValue":t[3]||(t[3]=s=>i(V)?V.value=s:null),label:"Password",autocomplete:"on",type:"password",placeholder:"············"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(R,{modelValue:a(n),"onUpdate:modelValue":t[4]||(t[4]=s=>i(n)?n.value=s:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",class:"d-flex gap-4"},{default:l(()=>[e(x,{type:"submit"},{default:l(()=>[v(" Submit ")]),_:1}),e(x,{type:"reset",color:"secondary",variant:"tonal"},{default:l(()=>[v(" Reset ")]),_:1})]),_:1})]),_:1})]),_:1}))}},Xe=c("label",{for:"firstNameHorizontalIcons"},"First Name",-1),el=c("label",{for:"emailHorizontalIcons"},"Email",-1),ll=c("label",{for:"mobileHorizontalIcons"},"Mobile",-1),ol=c("label",{for:"passwordHorizontalIcons"},"Password",-1),al={__name:"DemoFormLayoutHorizontalFormWithIcons",setup(F){const f=r(""),b=r(""),d=r(),V=r(),n=r(!1);return(w,t)=>(_(),k(g,{onSubmit:I(()=>{},["prevent"])},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[Xe]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"firstNameHorizontalIcons",modelValue:a(f),"onUpdate:modelValue":t[0]||(t[0]=s=>i(f)?f.value=s:null),"prepend-inner-icon":"ri-user-line",placeholder:"John","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[el]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"emailHorizontalIcons",modelValue:a(b),"onUpdate:modelValue":t[1]||(t[1]=s=>i(b)?b.value=s:null),"prepend-inner-icon":"ri-mail-line",placeholder:"johndoe@email.com","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[ll]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"mobileHorizontalIcons",modelValue:a(d),"onUpdate:modelValue":t[2]||(t[2]=s=>i(d)?d.value=s:null),type:"number","prepend-inner-icon":"ri-smartphone-line",placeholder:"+1 123 456 7890","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[ol]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"passwordHorizontalIcons",modelValue:a(V),"onUpdate:modelValue":t[3]||(t[3]=s=>i(V)?V.value=s:null),"prepend-inner-icon":"ri-lock-line",autocomplete:"on",type:"password",placeholder:"············","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(R,{modelValue:a(n),"onUpdate:modelValue":t[4]||(t[4]=s=>i(n)?n.value=s:null),label:"Remember me"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{"offset-md":"3",cols:"12",md:"9",class:"d-flex gap-4"},{default:l(()=>[e(x,{type:"submit"},{default:l(()=>[v(" Submit ")]),_:1}),e(x,{color:"secondary",type:"reset",variant:"tonal"},{default:l(()=>[v(" Reset ")]),_:1})]),_:1})]),_:1})]),_:1}))}},tl=c("label",{for:"firstName"},"First Name",-1),sl=c("label",{for:"email"},"Email",-1),dl=c("label",{for:"mobile"},"Mobile",-1),nl=c("label",{for:"password"},"Password",-1),rl={__name:"DemoFormLayoutHorizontalForm",setup(F){const f=r(""),b=r(""),d=r(),V=r(),n=r(!1);return(w,t)=>(_(),k(g,{onSubmit:I(()=>{},["prevent"])},{default:l(()=>[e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[tl]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"firstName",modelValue:a(f),"onUpdate:modelValue":t[0]||(t[0]=s=>i(f)?f.value=s:null),placeholder:"John","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[sl]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"email",modelValue:a(b),"onUpdate:modelValue":t[1]||(t[1]=s=>i(b)?b.value=s:null),placeholder:"johndoe@email.com","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[dl]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"mobile",modelValue:a(d),"onUpdate:modelValue":t[2]||(t[2]=s=>i(d)?d.value=s:null),type:"number",placeholder:"+1 123 456 7890","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"},{default:l(()=>[nl]),_:1}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(m,{id:"password",modelValue:a(V),"onUpdate:modelValue":t[3]||(t[3]=s=>i(V)?V.value=s:null),autocomplete:"on",type:"password",placeholder:"············","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(h,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(R,{modelValue:a(n),"onUpdate:modelValue":t[4]||(t[4]=s=>i(n)?n.value=s:null),label:"Remember me"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{"offset-md":"3",cols:"12",md:"9",class:"d-flex gap-4"},{default:l(()=>[e(x,{type:"submit"},{default:l(()=>[v(" Submit ")]),_:1}),e(x,{color:"secondary",variant:"tonal",type:"reset"},{default:l(()=>[v(" Reset ")]),_:1})]),_:1})]),_:1})]),_:1}))}},ml={ts:`<script lang="ts" setup>
const username = ref('')
const email = ref('')
const password = ref<string>()
const checkbox = ref(false)
const items = ['foo', 'bar', 'fizz', 'buzz'] as const
const values = ref<typeof items[number][]>([])
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <!-- 👉 Username -->
        <VTextField
          v-model="username"
          label="Username"
          placeholder="Johndoe"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Email -->
        <VTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Password -->
        <VTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          persistent-hint
          placeholder="············"
          hint="Your password must be 8-20 characters long."
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Autocomplete -->
        <VAutocomplete
          v-model="values"
          :items="items"
          chips
          multiple
          label="Autocomplete"
          placeholder="Select"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Checkbox -->
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <!-- 👉 submit and reset button -->
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const username = ref('')
const email = ref('')
const password = ref()
const checkbox = ref(false)

const items = [
  'foo',
  'bar',
  'fizz',
  'buzz',
]

const values = ref([])
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <!-- 👉 Username -->
        <VTextField
          v-model="username"
          label="Username"
          placeholder="Johndoe"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Email -->
        <VTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Password -->
        <VTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          persistent-hint
          placeholder="············"
          hint="Your password must be 8-20 characters long."
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Autocomplete -->
        <VAutocomplete
          v-model="values"
          :items="items"
          chips
          multiple
          label="Autocomplete"
          placeholder="Select"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Checkbox -->
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <!-- 👉 submit and reset button -->
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},il={ts:`<script lang="ts" setup>
import type { VForm } from 'vuetify/components/VForm'

const name = ref('')
const email = ref('')
const items = ['Item 1', 'Item 2', 'Item 3', 'Item 4'] as const
const select = ref<typeof items[number]>()
const checkbox = ref(false)
const form = ref<VForm>()
<\/script>

<template>
  <VForm
    ref="form"
    lazy-validation
  >
    <VRow>
      <VCol cols="12">
        <VTextField
          v-model="name"
          :rules="[requiredValidator]"
          label="Name"
          placeholder="John Doe"
          required
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="email"
          :rules="[emailValidator, requiredValidator]"
          label="E-mail"
          placeholder="johndoe@email.com"
          required
        />
      </VCol>

      <VCol cols="12">
        <VSelect
          v-model="select"
          :items="items"
          :rules="[requiredValidator]"
          placeholder="Select an Item"
          label="Item"
          name="select"
          require
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          :rules="[requiredValidator]"
          label="Do you agree?"
          required
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex flex-wrap gap-4"
      >
        <VBtn
          color="success"
          @click="form?.validate()"
        >
          Validate
        </VBtn>

        <VBtn
          color="error"
          @click="form?.reset()"
        >
          Reset Form
        </VBtn>

        <VBtn
          color="warning"
          @click="form?.resetValidation()"
        >
          Reset Validation
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const name = ref('')
const email = ref('')

const items = [
  'Item 1',
  'Item 2',
  'Item 3',
  'Item 4',
]

const select = ref()
const checkbox = ref(false)
const form = ref()
<\/script>

<template>
  <VForm
    ref="form"
    lazy-validation
  >
    <VRow>
      <VCol cols="12">
        <VTextField
          v-model="name"
          :rules="[requiredValidator]"
          label="Name"
          placeholder="John Doe"
          required
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="email"
          :rules="[emailValidator, requiredValidator]"
          label="E-mail"
          placeholder="johndoe@email.com"
          required
        />
      </VCol>

      <VCol cols="12">
        <VSelect
          v-model="select"
          :items="items"
          :rules="[requiredValidator]"
          placeholder="Select an Item"
          label="Item"
          name="select"
          require
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          :rules="[requiredValidator]"
          label="Do you agree?"
          required
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex flex-wrap gap-4"
      >
        <VBtn
          color="success"
          @click="form?.validate()"
        >
          Validate
        </VBtn>

        <VBtn
          color="error"
          @click="form?.reset()"
        >
          Reset Form
        </VBtn>

        <VBtn
          color="warning"
          @click="form?.resetValidation()"
        >
          Reset Validation
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},ul={ts:`<script lang="ts" setup>
const tab = ref('personal-info')
const firstName = ref('')
const lastName = ref('')
const country = ref()
const birthDate = ref('')
const phoneNo = ref<number>()
const countryList = ['USA', 'Canada', 'UK', 'Denmark', 'Germany', 'Iceland', 'Israel', 'Mexico']
const languageList = ['English', 'German', 'French', 'Spanish', 'Portuguese', 'Russian', 'Korean'] as const
const username = ref('')
const email = ref('')
const password = ref('')
const cPassword = ref('')
const twitterLink = ref('')
const facebookLink = ref('')
const googlePlusLink = ref('')
const linkedInLink = ref('')
const instagramLink = ref('')
const quoraLink = ref('')
const languages = ref<typeof languageList[number][]>([])
const isPasswordVisible = ref(false)
const isCPasswordVisible = ref(false)
<\/script>

<template>
  <VTabs v-model="tab">
    <VTab value="personal-info">
      Personal Info
    </VTab>
    <VTab value="account-details">
      Account Details
    </VTab>
    <VTab value="social-links">
      Social Links
    </VTab>
  </VTabs>

  <VCard flat>
    <VCardText>
      <VWindow
        v-model="tab"
        class="disable-tab-transition"
      >
        <VWindowItem value="personal-info">
          <VForm class="mt-2">
            <VRow>
              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="firstName"
                  label="First name"
                  placeholder="John"
                />
              </VCol>

              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="lastName"
                  label="Last name"
                  placeholder="Doe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="country"
                  :items="countryList"
                  label="Country"
                  placeholder="Select Country"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="languages"
                  :items="languageList"
                  multiple
                  chips
                  clearable
                  label="Language"
                  placeholder="Select Language"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppDateTimePicker
                  v-model="birthDate"
                  label="Birth Date"
                  placeholder="Select Birth Date"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="phoneNo"
                  type="number"
                  label="Phone No."
                  placeholder="+1 123 456 7890"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="account-details">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="username"
                  label="Username"
                  placeholder="Johndoe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="email"
                  label="Email"
                  suffix="@example.com"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="cPassword"
                  label="Confirm Password"
                  placeholder="············"
                  :type="isCPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isCPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isCPasswordVisible = !isCPasswordVisible"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="social-links">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="twitterLink"
                  label="Twitter"
                  placeholder="https://twitter.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="facebookLink"
                  label="Facebook"
                  placeholder="https://facebook.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="googlePlusLink"
                  label="Google+"
                  placeholder="https://plus.google.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="linkedInLink"
                  label="LinkedIn"
                  placeholder="https://linkedin.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="instagramLink"
                  label="Instagram"
                  placeholder="https://instagram.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="quoraLink"
                  label="Quora"
                  placeholder="https://quora.com/username"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>
      </VWindow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex gap-4">
      <VBtn>Submit</VBtn>
      <VBtn
        color="secondary"
        variant="tonal"
      >
        Cancel
      </VBtn>
    </VCardText>
  </VCard>
</template>
`,js:`<script setup>
const tab = ref('personal-info')
const firstName = ref('')
const lastName = ref('')
const country = ref()
const birthDate = ref('')
const phoneNo = ref()

const countryList = [
  'USA',
  'Canada',
  'UK',
  'Denmark',
  'Germany',
  'Iceland',
  'Israel',
  'Mexico',
]

const languageList = [
  'English',
  'German',
  'French',
  'Spanish',
  'Portuguese',
  'Russian',
  'Korean',
]

const username = ref('')
const email = ref('')
const password = ref('')
const cPassword = ref('')
const twitterLink = ref('')
const facebookLink = ref('')
const googlePlusLink = ref('')
const linkedInLink = ref('')
const instagramLink = ref('')
const quoraLink = ref('')
const languages = ref([])
const isPasswordVisible = ref(false)
const isCPasswordVisible = ref(false)
<\/script>

<template>
  <VTabs v-model="tab">
    <VTab value="personal-info">
      Personal Info
    </VTab>
    <VTab value="account-details">
      Account Details
    </VTab>
    <VTab value="social-links">
      Social Links
    </VTab>
  </VTabs>

  <VCard flat>
    <VCardText>
      <VWindow
        v-model="tab"
        class="disable-tab-transition"
      >
        <VWindowItem value="personal-info">
          <VForm class="mt-2">
            <VRow>
              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="firstName"
                  label="First name"
                  placeholder="John"
                />
              </VCol>

              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="lastName"
                  label="Last name"
                  placeholder="Doe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="country"
                  :items="countryList"
                  label="Country"
                  placeholder="Select Country"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="languages"
                  :items="languageList"
                  multiple
                  chips
                  clearable
                  label="Language"
                  placeholder="Select Language"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppDateTimePicker
                  v-model="birthDate"
                  label="Birth Date"
                  placeholder="Select Birth Date"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="phoneNo"
                  type="number"
                  label="Phone No."
                  placeholder="+1 123 456 7890"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="account-details">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="username"
                  label="Username"
                  placeholder="Johndoe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="email"
                  label="Email"
                  suffix="@example.com"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="cPassword"
                  label="Confirm Password"
                  placeholder="············"
                  :type="isCPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isCPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                  @click:append-inner="isCPasswordVisible = !isCPasswordVisible"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="social-links">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="twitterLink"
                  label="Twitter"
                  placeholder="https://twitter.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="facebookLink"
                  label="Facebook"
                  placeholder="https://facebook.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="googlePlusLink"
                  label="Google+"
                  placeholder="https://plus.google.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="linkedInLink"
                  label="LinkedIn"
                  placeholder="https://linkedin.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="instagramLink"
                  label="Instagram"
                  placeholder="https://instagram.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="quoraLink"
                  label="Quora"
                  placeholder="https://quora.com/username"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>
      </VWindow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex gap-4">
      <VBtn>Submit</VBtn>
      <VBtn
        color="secondary"
        variant="tonal"
      >
        Cancel
      </VBtn>
    </VCardText>
  </VCard>
</template>
`},cl={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 First Name -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="firstName">First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="firstName"
              v-model="firstName"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Email -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="email">Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="email"
              v-model="email"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Mobile -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="mobile">Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="mobile"
              v-model="mobile"
              type="number"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Password -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="password">Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="password"
              v-model="password"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol
        offset-md="3"
        cols="12"
        md="9"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>
        <VBtn
          color="secondary"
          variant="tonal"
          type="reset"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref()
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 First Name -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="firstName">First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="firstName"
              v-model="firstName"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Email -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="email">Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="email"
              v-model="email"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Mobile -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="mobile">Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="mobile"
              v-model="mobile"
              type="number"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Password -->
          <VCol
            cols="12"
            md="3"
          >
            <label for="password">Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="password"
              v-model="password"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol
        offset-md="3"
        cols="12"
        md="9"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>
        <VBtn
          color="secondary"
          variant="tonal"
          type="reset"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},Vl={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="firstNameHorizontalIcons">First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="firstNameHorizontalIcons"
              v-model="firstName"
              prepend-inner-icon="ri-user-line"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Email -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="emailHorizontalIcons">Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="emailHorizontalIcons"
              v-model="email"
              prepend-inner-icon="ri-mail-line"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Mobile -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="mobileHorizontalIcons">Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="mobileHorizontalIcons"
              v-model="mobile"
              type="number"
              prepend-inner-icon="ri-smartphone-line"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Password -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="passwordHorizontalIcons">Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="passwordHorizontalIcons"
              v-model="password"
              prepend-inner-icon="ri-lock-line"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Checkbox -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol
        offset-md="3"
        cols="12"
        md="9"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>
        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref()
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="firstNameHorizontalIcons">First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="firstNameHorizontalIcons"
              v-model="firstName"
              prepend-inner-icon="ri-user-line"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Email -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="emailHorizontalIcons">Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="emailHorizontalIcons"
              v-model="email"
              prepend-inner-icon="ri-mail-line"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Mobile -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="mobileHorizontalIcons">Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="mobileHorizontalIcons"
              v-model="mobile"
              type="number"
              prepend-inner-icon="ri-smartphone-line"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Password -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          >
            <label for="passwordHorizontalIcons">Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <VTextField
              id="passwordHorizontalIcons"
              v-model="password"
              prepend-inner-icon="ri-lock-line"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Checkbox -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol
        offset-md="3"
        cols="12"
        md="9"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>
        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},pl={ts:`<script lang="ts" setup>
const firstName = ref('')
const lastName = ref('')
const city = ref('')
const country = ref('')
const company = ref('')
const email = ref('')
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <!-- 👉 Last Name -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="lastName"
          label="Last Name"
          placeholder="Doe"
        />
      </VCol>

      <!-- 👉 Email -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="email"
          label="Email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <!-- 👉 City -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="city"
          label="City"
          placeholder="New York"
        />
      </VCol>

      <!-- 👉 Country -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="country"
          label="Country"
          placeholder="United States"
        />
      </VCol>

      <!-- 👉 Company -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="company"
          label="Company"
          placeholder="Pixinvent"
        />
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const lastName = ref('')
const city = ref('')
const country = ref('')
const company = ref('')
const email = ref('')
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <!-- 👉 Last Name -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="lastName"
          label="Last Name"
          placeholder="Doe"
        />
      </VCol>

      <!-- 👉 Email -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="email"
          label="Email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <!-- 👉 City -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="city"
          label="City"
          placeholder="New York"
        />
      </VCol>

      <!-- 👉 Country -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="country"
          label="Country"
          placeholder="United States"
        />
      </VCol>

      <!-- 👉 Company -->
      <VCol
        cols="12"
        md="6"
      >
        <VTextField
          v-model="company"
          label="Company"
          placeholder="Pixinvent"
        />
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},fl={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref('')
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <VTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="mobile"
          label="Mobile"
          placeholder="+1 123 456 7890"
          type="number"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref('')
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <VTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="mobile"
          label="Mobile"
          placeholder="+1 123 456 7890"
          type="number"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},bl={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref('')
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent>
    <VRow>
      <VCol cols="12">
        <VTextField
          v-model="firstName"
          prepend-inner-icon="ri-user-line"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="email"
          prepend-inner-icon="ri-mail-line"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="mobile"
          prepend-inner-icon="ri-smartphone-line"
          label="Mobile"
          placeholder="+1 123 456 7890"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="password"
          prepend-inner-icon="ri-lock-line"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          class="me-4"
        >
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref('')
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent>
    <VRow>
      <VCol cols="12">
        <VTextField
          v-model="firstName"
          prepend-inner-icon="ri-user-line"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="email"
          prepend-inner-icon="ri-mail-line"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="mobile"
          prepend-inner-icon="ri-smartphone-line"
          label="Mobile"
          placeholder="+1 123 456 7890"
        />
      </VCol>

      <VCol cols="12">
        <VTextField
          v-model="password"
          prepend-inner-icon="ri-lock-line"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          class="me-4"
        >
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},Cl=c("h4",{class:"text-h6 font-weight-medium mb-5"}," Collapsible Section ",-1),vl=c("h4",{class:"text-h6 font-weight-medium mb-5"}," Sticky Section ",-1),Hl={__name:"form-layouts",setup(F){return(f,b)=>{const d=rl,V=Fe,n=al,w=Ze,t=Qe,s=Ke,u=Oe,y=Ge,U=Ye,P=We,S=ze;return _(),q("div",null,[e(h,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Horizontal Form",code:cl},{default:l(()=>[e(d)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Horizontal Form with Icons",code:Vl},{default:l(()=>[e(n)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Vertical Form",code:fl},{default:l(()=>[e(w)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Vertical Form with Icons",code:bl},{default:l(()=>[e(t)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(V,{title:"Multiple Column",code:pl},{default:l(()=>[e(s)]),_:1},8,["code"])]),_:1})]),_:1}),e(h,{class:"match-height my-3"},{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Form Hint",code:ml},{default:l(()=>[e(u)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Form Validation",code:il},{default:l(()=>[e(y)]),_:1},8,["code"])]),_:1})]),_:1}),e(h,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(V,{title:"Form with Tabs","no-padding":"",code:ul},{default:l(()=>[e(U)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[Cl,e(P)]),_:1}),e(o,{cols:"12"},{default:l(()=>[vl,e(S)]),_:1})]),_:1})])}}};export{Hl as default};
