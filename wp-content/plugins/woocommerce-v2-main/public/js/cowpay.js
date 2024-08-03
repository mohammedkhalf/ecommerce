var styles = "\n.cowpay-payment-container {\n    position: relative;\n    width: 100%;\n  }\n  \n  .cowpay-payment-responsive-iframe {\n    position: relative;\n    top: 0;\n    left: 0;\n    bottom: 0;\n    right: 0;\n    width: 100%;\n    height:1200px;\n    border: none;\n  }\n", 
styleSheet = document.createElement("style"); 
let data; 
styleSheet.innerText = styles, document.head.appendChild(styleSheet); 
const Cowpay = { mount: function (e) { document.getElementById(e).appendChild(frame.buildIframe(frame.constructIframeLink(data))) }, 
checkout: function (e, t) { return data = { clientSecret: e, iframecode: t }, this } }; 
var frame = { buildIframe: function (e) { var t = document.getElementById("ifrm"); if (null != t) return t; 
var n = document.createElement("div"); n.classList.add("cowpay-payment-container"); 
var a = document.createElement("iframe"); return a.setAttribute("id", "ifrm"), 
a.classList.add("cowpay-payment-responsive-iframe"), a.setAttribute("src", e), n.appendChild(a), n }, 
constructIframeLink: e => "https://dashboard.cowpay.me:8070/customer-iframe?intention=" + e.clientSecret + "&frameCode=" + e.iframecode };