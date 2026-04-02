import{j as e,m as y}from"./vendor-motion-8cwGCuEa.js";import{r as l,H as A,a as j}from"./vendor-inertia-CUiX090b.js";import{c as w}from"./utils-BJit6Gfs.js";import"./vendor-recharts-Dt5P34Bi.js";/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=(...s)=>s.filter((a,i,o)=>!!a&&a.trim()!==""&&o.indexOf(a)===i).join(" ").trim();/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const S=s=>s.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const L=s=>s.replace(/^([A-Z])|[\s-_]+(\w)/g,(a,i,o)=>o?o.toUpperCase():i.toLowerCase());/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const v=s=>{const a=L(s);return a.charAt(0).toUpperCase()+a.slice(1)};/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var k={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor",strokeWidth:2,strokeLinecap:"round",strokeLinejoin:"round"};/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const P=s=>{for(const a in s)if(a.startsWith("aria-")||a==="role"||a==="title")return!0;return!1},M=l.createContext({}),$=()=>l.useContext(M),z=l.forwardRef(({color:s,size:a,strokeWidth:i,absoluteStrokeWidth:o,className:d="",children:r,iconNode:h,...x},m)=>{const{size:n=24,strokeWidth:u=2,absoluteStrokeWidth:g=!1,color:f="currentColor",className:p=""}=$()??{},t=o??g?Number(i??u)*24/Number(a??n):i??u;return l.createElement("svg",{ref:m,...k,width:a??n??k.width,height:a??n??k.height,stroke:s??f,strokeWidth:t,className:C("lucide",p,d),...!r&&!P(x)&&{"aria-hidden":"true"},...x},[...h.map(([b,_])=>l.createElement(b,_)),...Array.isArray(r)?r:[r]])});/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const c=(s,a)=>{const i=l.forwardRef(({className:o,...d},r)=>l.createElement(z,{ref:r,iconNode:a,className:C(`lucide-${S(v(s))}`,`lucide-${s}`,o),...d}));return i.displayName=v(s),i};/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const E=[["path",{d:"M5 12h14",key:"1ays0h"}],["path",{d:"m12 5 7 7-7 7",key:"xquz4c"}]],R=c("arrow-right",E);/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const I=[["path",{d:"M20 6 9 17l-5-5",key:"1gmf2c"}]],N=c("check",I);/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const W=[["rect",{width:"20",height:"14",x:"2",y:"5",rx:"2",key:"ynyp8z"}],["line",{x1:"2",x2:"22",y1:"10",y2:"10",key:"1b3vmo"}]],D=c("credit-card",W);/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const H=[["path",{d:"M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z",key:"1vdc57"}],["path",{d:"M5 21h14",key:"11awu3"}]],V=c("crown",H);/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const U=[["path",{d:"M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z",key:"oel41y"}]],B=c("shield",U);/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const K=[["path",{d:"M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z",key:"qn84l0"}],["path",{d:"M13 5v2",key:"dyzc3o"}],["path",{d:"M13 17v2",key:"1ont0d"}],["path",{d:"M13 11v2",key:"1wjjxi"}]],Z=c("ticket",K);/**
 * @license lucide-react v1.7.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const q=[["path",{d:"M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z",key:"1xq2db"}]],O=c("zap",q);function Q({packages:s}){var p;const[a,i]=l.useState((p=s[0])==null?void 0:p.id),[o,d]=l.useState(""),[r,h]=l.useState(null),[x,m]=l.useState(!1),n=s.find(t=>t.id===a)||s[0],u=["Input via chat tanpa batas","Laporan bulanan Excel & Email","Dashboard analisis keuangan lengkap","Auto-backup data ke Google Drive","Multi-account management"],g=async()=>{if(o){m(!0);try{const t=await j.post(route("vouchers.validate"),{code:o,package_id:a});h(t.data)}catch{alert("Kode voucher tidak valid atau sudah kedaluwarsa."),h(null)}finally{m(!1)}}},f=async()=>{try{const t=await j.post(route("checkout.process"),{package_id:a,voucher_id:r==null?void 0:r.voucher_id});alert(`Order ${t.data.order.order_number} berhasil dibuat!`)}catch{alert("Terjadi kesalahan saat memproses pembayaran.")}};return e.jsxs("div",{className:"min-h-screen bg-slate-50",children:[e.jsx(A,{title:"Unlock Premium"}),e.jsxs("div",{className:"max-w-screen-sm mx-auto min-h-screen bg-white shadow-xl shadow-slate-200",children:[e.jsxs("div",{className:"p-8 pb-32",children:[e.jsxs("div",{className:"text-center mb-10 mt-6",children:[e.jsx(y.div,{initial:{scale:.8,opacity:0},animate:{scale:1,opacity:1},className:"inline-flex h-20 w-20 items-center justify-center rounded-[32px] bg-indigo-600 shadow-2xl shadow-indigo-200 mb-6",children:e.jsx(B,{className:"w-10 h-10 text-white"})}),e.jsx("h1",{className:"text-2xl font-black text-slate-900 mb-2",children:"Buka Akses Premium"}),e.jsx("p",{className:"text-sm text-slate-500 max-w-xs mx-auto",children:"Kelola keuanganmu lebih cerdas dengan fitur Power-Ups eksklusif."})]}),e.jsx("div",{className:"space-y-3 mb-10",children:u.map((t,b)=>e.jsxs(y.div,{initial:{x:-20,opacity:0},animate:{x:0,opacity:1},transition:{delay:b*.1},className:"flex items-center gap-3",children:[e.jsx("div",{className:"h-6 w-6 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0",children:e.jsx(N,{className:"w-3.5 h-3.5 text-emerald-600 stroke-[3]"})}),e.jsx("span",{className:"text-[13px] font-bold text-slate-700 leading-tight",children:t})]},b))}),e.jsxs("div",{className:"space-y-4 mb-8",children:[e.jsx("div",{className:"text-xs font-bold text-slate-400 uppercase tracking-widest ml-1",children:"Pilih Paket"}),s.map(t=>e.jsxs("label",{className:w("relative block p-5 rounded-3xl border-2 transition-all cursor-pointer overflow-hidden",a===t.id?"bg-indigo-50/50 border-indigo-600":"bg-white border-slate-100 hover:border-slate-200"),children:[e.jsx("input",{type:"radio",name:"plan",value:t.id,className:"sr-only",onChange:()=>i(t.id)}),t.duration_days>=365&&e.jsx("div",{className:"absolute top-0 right-0 py-1.5 px-4 bg-indigo-600 text-white italic text-[10px] font-black rounded-bl-2xl",children:"POPULER"}),e.jsxs("div",{className:"flex items-center justify-between",children:[e.jsxs("div",{className:"flex items-center gap-4",children:[e.jsx("div",{className:w("h-12 w-12 rounded-2xl flex items-center justify-center",t.duration_days>=365?"bg-indigo-50 text-indigo-600":"bg-amber-50 text-amber-600"),children:t.duration_days>=365?e.jsx(V,{className:"w-6 h-6"}):e.jsx(O,{className:"w-6 h-6"})}),e.jsxs("div",{children:[e.jsx("div",{className:"text-base font-black text-slate-900",children:t.name}),e.jsxs("div",{className:"flex items-center gap-2 mt-0.5",children:[e.jsxs("span",{className:"text-xs font-bold text-slate-400 line-through",children:["Rp ",(t.price*1.5).toLocaleString("id-ID")]}),e.jsx("span",{className:"text-[10px] bg-rose-100 text-rose-600 font-bold px-2 py-0.5 rounded-full uppercase",children:"Hemat 33%"})]})]})]}),e.jsxs("div",{className:"text-right",children:[e.jsxs("div",{className:"text-lg font-black text-slate-900",children:["Rp ",(t.price/1e3).toLocaleString("id-ID"),"k"]}),e.jsxs("div",{className:"text-[10px] font-bold text-slate-400 uppercase tracking-tight",children:["/ ",t.duration_days," Hari"]})]})]})]},t.id))]}),e.jsxs("div",{className:"mb-6",children:[e.jsx("div",{className:"text-xs font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2",children:"Kode Voucher"}),e.jsxs("div",{className:"relative",children:[e.jsx(Z,{className:"absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"}),e.jsx("input",{type:"text",value:o,onChange:t=>d(t.target.value),placeholder:"Punya kode diskon?",className:"w-full h-14 pl-12 pr-28 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-indigo-600 text-sm font-bold uppercase tracking-widest placeholder:normal-case placeholder:tracking-normal"}),e.jsx("button",{onClick:g,disabled:!o||x,className:"absolute right-2 top-1/2 -translate-y-1/2 h-10 px-4 rounded-xl bg-slate-900 text-white text-[11px] font-black uppercase disabled:opacity-30 disabled:bg-slate-400",children:x?"...":"Apply"})]}),r&&e.jsxs("div",{className:"mt-2 text-[11px] font-bold text-emerald-600 flex items-center gap-1 ml-1",children:[e.jsx(N,{className:"w-3 h-3"})," Voucher berhasil diterapkan!"]})]})]}),e.jsxs("div",{className:"fixed bottom-0 left-0 right-0 max-w-screen-sm mx-auto p-6 bg-white border-t border-slate-100 flex items-center justify-between gap-4",children:[e.jsxs("div",{className:"hidden sm:block text-left",children:[e.jsx("div",{className:"text-[10px] font-bold text-slate-400 uppercase",children:"Subtotal"}),e.jsxs("div",{className:"text-lg font-black text-slate-900",children:["Rp ",((r==null?void 0:r.total)||(n==null?void 0:n.price)||0).toLocaleString("id-ID")]})]}),e.jsxs("button",{onClick:f,className:"flex-1 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center gap-3 text-sm font-bold shadow-xl shadow-indigo-100 transition-transform active:scale-95 group",children:[e.jsx(D,{className:"w-5 h-5"})," Bayar Sekarang ",e.jsx(R,{className:"w-4 h-4 group-hover:translate-x-1 transition-transform"})]})]})]})]})}export{Q as default};
