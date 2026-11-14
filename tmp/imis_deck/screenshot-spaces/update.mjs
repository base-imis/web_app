import fs from 'node:fs/promises';
import {FileBlob,PresentationFile} from 'file:///C:/Program%20Files/WindowsApps/OpenAI.CodexPrimaryRuntime.v26-1112-611-0_26.1112.611.0_x64__3k8sg7r9htsxt/dependencies/node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs';
const B='C:/xampp/htdocs/lang_web_app/tmp/imis_deck';const O=B+'/screenshot-spaces';
const p=await PresentationFile.importPptx(await FileBlob.load('C:/Users/user/.codex/visualizations/2026/11/10/01a1c41d-eda3-75e0-8fbc-fc9ee0094637/IMIS_Mobile_App_Final_Reviewer_Updates.pptx'));
const old=[...p.slides.items];const blue='#194A75',ink='#1C2938';
function txt(s,t,x,y,w,h,size=24,color=ink,bold=false,fill='none'){
 const a=s.shapes.add({geometry:'textbox',name:t.slice(0,45)||'Template content clearing',position:{left:x,top:y,width:w,height:h},fill,line:{fill:'none',width:0}});a.text=t;a.text.style={fontSize:size,typeface:'Times New Roman',color,bold,insets:0,autoFit:'none',wrap:'square'};return a;
}
function frame(s,title){
 for(const a of s.shapes.items){a.text='';a.fill='none';a.line={fill:'none',width:0};}
 txt(s,'',12,150,1256,500,24,ink,false,'#FFFFFF');txt(s,'',530,70,500,45,24,ink,false,'#FFFFFF');
 const h=txt(s,'Feature walkthrough',440,65,730,65,32,blue);h.text.alignment='center';txt(s,title,72,169,1130,42,24,blue,true);
}
function rows(s,data){data.forEach((v,i)=>{const y=233+i*123;txt(s,String(i+1).padStart(2,'0'),64,y,55,45,32,blue,true);txt(s,v[0],142,y,680,40,25,ink,true);txt(s,v[1],142,y+43,680,77,23,'#3B4D5E');});}
function space(s,label,screen){
 const cap=txt(s,label,895,213,300,31,23,blue,true);cap.text.alignment='center';
 s.shapes.add({geometry:'rect',name:'Screenshot space: '+label,position:{left:932,top:252,width:226,height:370},fill:'#F7FAFC',line:{style:'dashed',fill:'#7B9DB6',width:1.5}});
 const t=txt(s,'Add actual app\nscreenshot here\n\n'+screen,947,360,196,158,22,'#587487');t.text.alignment='center';
}
// Remove the decorative app icon from the Building intro so the image space is clear.
const records=await p.inspect({select:'$.slides[2].elements[?(@.kind == "image")]',include:['id']});
for(const r of records.records){const im=p.resolve(r.id);if(im.frame.width<500)p.delete(r.id);}
frame(old[2],'Building footprint capture and review');rows(old[2],[['Building Map','Capture the footprint by tapping and adjusting polygon vertices.'],['Buildings Data and KML Viewer','Review the saved KML outline and locally collected record.'],['Upload or ANF resolution','Upload the survey, or open the separate ANF location workflow.']]);space(old[2],'Building footprint','Building Map\nFootprint visible');
frame(old[3],'Emptying service');rows(old[3],[['Application List','Find the job using the list search and filters.'],['Trip form','Record the active emptying trip and review validation messages.'],['Trip sequence','Record the previous disposal before the next emptying trip opens.']]);space(old[3],'Emptying','Emptying trip form');
frame(old[4],'Sewer connection');rows(old[4],[['Sewer Map','Review the building and sewer network layers.'],['Connection form','Enter the relevant building identifier and sewer code.'],['Sewer Data','Review the collected record and submit it to the web app.']]);space(old[4],'Sewer connection','Sewer Map or\nconnection form');
const fstp=p.slides.add();fstp.images.add({blob:await fs.readFile(B+'/media/image-6-1.png'),contentType:'image/png',position:{left:0,top:0,width:1280,height:720}});
frame(fstp,'FSTP sludge disposal');rows(fstp,[['Sludge Collection','Open the application’s disposal record from the list.'],['Disposal form','Record trip disposal at the selected treatment plant.'],['Daily capacity','Open the capacity view in the header to review plant capacity.']]);space(fstp,'FSTP disposal','Sludge disposal form\nor capacity view');
fstp.speakerNotes.text=old[3].speakerNotes.text+'\nFSTP means fecal sludge treatment plant. The dashboard labels this workflow Sludge Collection. Source: src/components/headers/Header.tsx and src/service/supervisor_service.js.\nScreenshot insertion: add a real app capture showing sludge disposal or the daily capacity view in the reserved space. No feature screenshot was available in the supplied assets.';
for(const s of [old[2],old[3],old[4]])s.speakerNotes.text+='\nScreenshot insertion: replace the labeled rectangle with an actual app screenshot. Use a portrait crop that preserves the relevant feature controls. No screenshot for this feature was available in the supplied files.';
const inspected=await p.inspect({select:'$.slides[*]',include:['id']});const ids=inspected.records.map(r=>r.id);const map=new Map(p.slides.items.map((s,i)=>[s,ids[i]]));const order=[...old.slice(0,4),fstp,...old.slice(4)];p.slides.reorder(order.map(s=>map.get(s)));
await (await PresentationFile.exportPptx(p)).save(O+'/candidate.pptx');console.log('EXPORTED',p.slides.items.length);
for(const n of [3,4,5,6]){const blob=await p.export({slide:p.slides.items[n-1],format:'png',scale:1});await fs.writeFile(O+'/renders/slide-'+n+'.png',new Uint8Array(await blob.arrayBuffer()));console.log('RENDER',n);}
console.log('DONE');process.exit(0);
