const n=(e,a)=>{const r=(e.page-1)*e.itemsPerPage+1,g=Math.min(e.page*e.itemsPerPage,a);return`${a===0?0:r} - ${g} of ${a}`};export{n as p};
