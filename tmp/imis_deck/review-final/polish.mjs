import fs from 'node:fs/promises';
import {FileBlob,PresentationFile} from 'file:///C:/Program%20Files/WindowsApps/OpenAI.CodexPrimaryRuntime.v26-1112-611-0_26.1112.611.0_x64__3k8sg7r9htsxt/dependencies/node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs';
const B='C:/xampp/htdocs/lang_web_app/tmp/imis_deck/review-final';
const p=await PresentationFile.importPptx(await FileBlob.load(B+'/candidate.pptx'));
function at(n,x,y){const a=p.slides.items[n-1].shapes.items.find(a=>Math.abs(a.position.left-x)<1&&Math.abs(a.position.top-y)<1);if(!a)throw new Error(`Missing ${n} ${x} ${y}`);return a;}
at(1,280,353).text='Features and source code\nNew instance deployment';
at(6,72,169).text='Basic application workflow';
at(6,142,214).text='Startup and sign-in';at(6,142,254).text='App.js restores state, then navigation selects the permitted screens.';
at(6,142,340).text='Screen calls a service and the Axios client';at(6,142,380).text='The client attaches the token and sends the request to the selected web API.';
at(6,142,466).text='Backend validates and returns the result';at(6,142,506).text='The response updates the screen or Redux state for the user to review.';
at(6,142,214).text.style={fontSize:25,typeface:'Times New Roman',color:'#1C2938',bold:true,insets:0};
p.slides.items[5].speakerNotes.text+='\nCode workflow: screen event, feature service, shared Axios request interceptor, backend validation/storage, response handler, then local/Redux state. A successful local KML save is distinct from a server upload.';
at(22,142,267).text='GET /api/wms/{layer}, using buildings, roads, wards, containments or sewers.';
at(24,674,303).text='GEOSERVER_BBOX stores a fixed full extent for the web API response.\n\nUse minX,minY,maxX,maxY in EPSG:3857 meters.\n\nChanging it alone does not recenter every mobile map.';
const foot=at(21,72,594);foot.position={left:72,top:590,width:1135,height:60};foot.text='Registry: building_info.wms_links. Setup: database/seeders/WmsLinkSeeder.php\nAllowlist: config/gis.php. Prefix layer and style names with GEOSERVER_WORKSPACE.';foot.text.style={fontSize:21.5,typeface:'Times New Roman',color:'#194A75',bold:true,insets:0,autoFit:'none',wrap:'square'};
const t=p.slides.items[19].tables.items[0];t.getCell(6,0).value='GEOSERVER_BBOX, GEOSERVER_WIDTH,\nGEOSERVER_HEIGHT';t.rows[6].height=78;
for(let r=0;r<6;r++)t.rows[r].height=55;
for(const n of [1,6,20,21,22,24]){const blob=await p.export({slide:p.slides.items[n-1],format:'png',scale:1});await fs.writeFile(B+'/renders/slide-'+String(n).padStart(2,'0')+'.png',new Uint8Array(await blob.arrayBuffer()));console.log('REPAIR',n);}
await (await PresentationFile.exportPptx(p)).save(B+'/reviewed-candidate.pptx');console.log('EXPORTED');process.exit(0);
