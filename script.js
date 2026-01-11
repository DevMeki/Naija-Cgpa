// Ultimate CGPA Engine v3
let appState = {
  lvl: 100,
  maxLvl: 400,
  activeSemIndex: 1, // Default to 1st sem
  db: {}, // { lvl: { semIndex: { mode, type, items } } }
};

document.addEventListener("DOMContentLoaded", () => {
  load();
  initTabs();
  switchLevel(appState.lvl);
  calc();
});

function initTabs() {
  const container = document.getElementById("level-tabs");
  const addButton = container.querySelector(
    'button[onclick="addNewLevelUI()"]'
  );
  container.innerHTML = "";

  for (let l = 100; l <= appState.maxLvl; l += 100) {
    addTabButton(l, container);
  }
  container.appendChild(addButton);
}

function addTabButton(l, container) {
  const wrapper = document.createElement("div");
  wrapper.className = "relative group h-11 md:min-w-[120px] snap-start flex-shrink-0";

  const btn = document.createElement("button");
  btn.id = `tab-${l}`;
  btn.onclick = () => switchLevel(l);
  btn.className = "level-tab w-full h-full rounded-xl font-bold text-xs border border-slate-200 bg-white text-slate-500 transition-all hover:border-primary-light hover:shadow-md shadow-sm active:scale-95";
  btn.innerText = `${l}L`;

  // Delete Button
  const delBtn = document.createElement("button");
  delBtn.className =
    "absolute -top-1 -right-1 w-4 h-4 bg-rose-500/80 text-white rounded-full flex items-center justify-center text-[10px] transition-opacity z-10 shadow-lg";
  delBtn.innerHTML = "×";
  delBtn.onclick = (e) => {
    e.stopPropagation();
    deleteLevel(l);
  };

  wrapper.appendChild(btn);
  wrapper.appendChild(delBtn);
  container.appendChild(wrapper);

  // Update active state if needed
  if (appState.lvl === l) btn.classList.add("active-tab");
}

function deleteLevel(lvl) {
  if (confirm(`Delete ALL data for ${lvl}L?`)) {
    delete appState.db[lvl];

    // If it was the max level, decrease maxLvl
    if (lvl === appState.maxLvl && appState.maxLvl > 100) {
      appState.maxLvl -= 100;
    }

    // If we deleted the current level, switch to another
    if (appState.lvl === lvl) {
      appState.lvl = appState.maxLvl;
    }

    save();
    initTabs();
    switchLevel(appState.lvl);
    calc();
  }
}

function addNewLevelUI() {
  appState.maxLvl += 100;
  save();
  initTabs();
  switchLevel(appState.maxLvl);
}

function switchLevel(lvl) {
  appState.lvl = lvl;

  // UI: Update Chips
  document
    .querySelectorAll(".level-tab")
    .forEach((t) => t.classList.remove("active-tab"));
  const tab = document.getElementById(`tab-${lvl}`);
  if (tab) tab.classList.add("active-tab");

  // Re-render based on active semester
  switchSemester(appState.activeSemIndex);
}

function switchSemester(idx) {
  appState.activeSemIndex = idx;
  const lvl = appState.lvl;

  // UI: Update Toggle Buttons
  const btn1 = document.getElementById("sem-btn-1");
  const btn2 = document.getElementById("sem-btn-2");

  if (idx === 1) {
    btn1.className =
      "px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all bg-white text-primary shadow-sm";
    btn2.className =
      "px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600";
  } else {
    btn2.className =
      "px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all bg-white text-primary shadow-sm";
    btn1.className =
      "px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600";
  }

  const container = document.getElementById("semester-container");
  container.innerHTML = "";

  // Ensure data structure exists
  if (!appState.db[lvl]) appState.db[lvl] = {};
  if (!appState.db[lvl][idx]) {
    appState.db[lvl][idx] = {
      mode: "detail",
      type: "grade",
      items: [
        { u: "", g: "", s: "" },
        { u: "", g: "", s: "" },
        { u: "", g: "", s: "" },
        { u: "", g: "", s: "" },
      ],
    };
  }

  renderSem(lvl, idx);
  calc();
}

function renderSem(lvl, idx) {
  const s = appState.db[lvl][idx];
  const container = document.getElementById("semester-container");

  let tId = "tpl-empty";
  if (s.mode === "quick") tId = "tpl-quick";
  if (s.mode === "detail") tId = "tpl-detail";

  const tpl = document.getElementById(tId);
  const clone = tpl.content.cloneNode(true);
  const wrapper = document.createElement("div");
  wrapper.dataset.sem = idx;
  wrapper.appendChild(clone);

  // Set Labels
  const label = wrapper.querySelector(".sem-label");
  if (label)
    label.textContent = idx === 1 ? "First Semester" : "Second Semester";

  // Quick Mode Logic
  if (s.mode === "quick") {
    const iG = wrapper.querySelector(".inp-gpa");
    const iU = wrapper.querySelector(".inp-units");
    iG.value = s.gpa || "";
    iU.value = s.units || "";

    const update = () => {
      s.gpa = parseFloat(iG.value) || 0;
      s.units = parseFloat(iU.value) || 0;
      save();
      calc();
    };
    iG.oninput = iU.oninput = update;
  }

  // Detail Mode Logic
  if (s.mode === "detail") {
    const list = wrapper.querySelector(".course-list");
    if (s.items.length === 0) {
      for (let i = 0; i < 4; i++) s.items.push({ u: "", g: "", s: "" });
      save(); // Save the default items
    }

    updateToggleStyle(wrapper, s.type);

    // Set initial header label
    const headerLabel = wrapper.querySelector(".val-label");
    if (headerLabel)
      headerLabel.textContent = s.type === "score" ? "Score" : "Grade";

    s.items.forEach((item, iIdx) => {
      const row = document.getElementById("tpl-course").content.cloneNode(true);
      const inN = row.querySelector(".inp-name");
      const inU = row.querySelector(".inp-u");
      const inG = row.querySelector(".inp-g");
      const inS = row.querySelector(".inp-s");

      inN.value = item.n || "";
      inU.value = item.u || "";
      inG.value = item.g || "";
      inS.value = item.s || "";

      // Sync visibility
      if (s.type === "score") {
        inG.classList.add("hidden");
        inS.classList.remove("hidden");
      } else {
        inG.classList.remove("hidden");
        inS.classList.add("hidden");
      }

      const sync = () => {
        item.n = inN.value;
        item.u = parseFloat(inU.value) || 0;
        item.g = inG.value;
        item.s = inS.value;
        save();
        calc();
        syncMeta(wrapper);
      };
      inN.oninput = inU.oninput = inG.onchange = inS.oninput = sync;
      list.appendChild(row);
    });
    syncMeta(wrapper);
  }

  container.appendChild(wrapper);
}

function syncMeta(wrapper) {
  const sIdx = wrapper.dataset.sem;
  const s = appState.db[appState.lvl][sIdx];
  const meta = wrapper.querySelector(".sem-meta");
  if (!meta) return;

  let units = 0;
  const count = s.items.filter((i) => {
    if (i.u > 0) {
      units += i.u;
      return true;
    }
    return false;
  }).length;

  meta.textContent = `${count} COURSES • ${units} UNITS`;
}

// Action Functions
function setupSem(btn, mode) {
  const sIdx = btn.closest("[data-sem]").dataset.sem;
  const s = appState.db[appState.lvl][sIdx];
  s.mode = mode;
  if (mode === "detail" && s.items.length === 0) {
    for (let i = 0; i < 4; i++) s.items.push({ u: "", g: "", s: "" });
  }
  save();
  switchSemester(appState.activeSemIndex);
}

function resetSem(btn) {
  const sIdx = btn.closest("[data-sem]").dataset.sem;
  appState.db[appState.lvl][sIdx] = { mode: "empty", type: "grade", items: [] };
  save();
  switchLevel(appState.lvl);
  calc();
}

function addNewCourse(btn) {
  const sIdx = btn.closest("[data-sem]").dataset.sem;
  appState.db[appState.lvl][sIdx].items.push({ u: "", g: "", s: "" });
  save();
  switchSemester(appState.activeSemIndex);
}

function toggleInputType(btn, type) {
  const wrapper = btn.closest("[data-sem]");
  const sIdx = wrapper.dataset.sem;
  const s = appState.db[appState.lvl][sIdx];

  s.type = type;
  save();

  // Hot swap UI for all rows in this semester
  // Hot swap UI for all rows in this semester
  const rows = wrapper.querySelectorAll(".course-item");
  rows.forEach((row) => {
    const inG = row.querySelector(".inp-g");
    const inS = row.querySelector(".inp-s");
    if (type === "score") {
      inG.classList.add("hidden");
      inS.classList.remove("hidden");
    } else {
      inG.classList.remove("hidden");
      inS.classList.add("hidden");
    }
  });

  // Update Table Header
  const headerLabel = wrapper.querySelector(".val-label");
  if (headerLabel)
    headerLabel.textContent = type === "score" ? "Score" : "Grade";

  updateToggleStyle(wrapper, type);
  calc();
}

function updateToggleStyle(wrapper, type) {
  const bG = wrapper.querySelector(".btn-grade");
  const bS = wrapper.querySelector(".btn-score");
  if (type === "grade") {
    bG.className =
      "btn-grade px-4 py-1.5 rounded-lg text-[9px] font-black transition-all bg-indigo-600 text-white shadow-md shadow-indigo-600/10";
    bS.className =
      "btn-score px-4 py-1.5 rounded-lg text-[9px] font-black transition-all text-slate-400 hover:text-slate-600";
  } else {
    bS.className =
      "btn-score px-4 py-1.5 rounded-lg text-[9px] font-black transition-all bg-indigo-600 text-white shadow-md shadow-indigo-600/10";
    bG.className =
      "btn-grade px-4 py-1.5 rounded-lg text-[9px] font-black transition-all text-slate-400 hover:text-slate-600";
  }
}

function calc() {
  let globalU = 0;
  let globalQP = 0;

  Object.keys(appState.db).forEach((lvl) => {
    Object.keys(appState.db[lvl]).forEach((sIdx) => {
      const s = appState.db[lvl][sIdx];
      if (s.mode === "quick") {
        if (s.units > 0) {
          globalU += s.units;
          globalQP += s.units * s.gpa;
        }
      } else if (s.mode === "detail") {
        s.items.forEach((item) => {
          if (item.u > 0) {
            let gp = -1;
            if (s.type === "score") gp = scoreToGP(item.s);
            else gp = item.g !== "" ? parseFloat(item.g) : -1;

            if (gp !== -1) {
              globalU += item.u;
              globalQP += item.u * gp;
            }
          }
        });
      }
    });
  });

  const cgpa = globalU > 0 ? globalQP / globalU : 0;
  document.getElementById("global-cgpa").textContent = cgpa.toFixed(2);
  document.getElementById("global-units").textContent = `${globalU} Units`;

  // Update current semester GPA display based on ACTIVE semester
  const currentLvl = appState.lvl;
  const activeIdx = appState.activeSemIndex || 1;
  const sActive = appState.db[currentLvl]?.[activeIdx];
  let semGPA = 0;
  if (sActive) {
    if (sActive.mode === "quick") semGPA = sActive.gpa || 0;
    else {
      let sU = 0,
        sQP = 0;
      sActive.items.forEach((i) => {
        if (i.u > 0) {
          let gp =
            sActive.type === "score"
              ? scoreToGP(i.s)
              : i.g !== ""
              ? parseFloat(i.g)
              : -1;
          if (gp !== -1) {
            sU += i.u;
            sQP += i.u * gp;
          }
        }
      });
      semGPA = sU > 0 ? sQP / sU : 0;
    }
  }
  document.getElementById("sem-gpa-val").textContent = semGPA.toFixed(2);

  let cls = "No Data";
  let bg = "bg-slate-100";
  let text = "text-slate-400";

  if (globalU > 0) {
    if (cgpa >= 4.5) {
      cls = "1st Class Honours";
      bg = "bg-green-500";
      text = "text-white";
    } else if (cgpa >= 3.5) {
      cls = "2nd Class Upper (2:1)";
      bg = "bg-blue-500";
      text = "text-white";
    } else if (cgpa >= 2.4) {
      cls = "2nd Class Lower (2:2)";
      bg = "bg-orange-500";
      text = "text-white";
    } else if (cgpa >= 1.5) {
      cls = "3rd Class Honours";
      bg = "bg-slate-700";
      text = "text-white";
    } else {
      cls = "Pass / Fail";
      bg = "bg-rose-500";
      text = "text-white";
    }
  }

  const disp = document.getElementById("global-class");
  disp.textContent = cls;
  disp.className = `inline-block px-4 py-1.5 rounded-full text-[10px] font-black uppercase transition-all shadow-md ${bg} ${text}`;

  // Ensure CGPA text is always white on the blue gradient, but maybe give it a subtle glow if it's high
  const cgpaEl = document.getElementById("global-cgpa");
  cgpaEl.textContent = cgpa.toFixed(2);
  if (cgpa >= 4.5)
    cgpaEl.className =
      "text-5xl font-extrabold text-white drop-shadow-[0_0_15px_rgba(255,255,255,0.3)]";
  else cgpaEl.className = "text-5xl font-extrabold text-white";
}

function scoreToGP(s) {
  if (s === "" || isNaN(s)) return -1;
  const n = parseFloat(s);
  if (n >= 70) return 5;
  if (n >= 60) return 4;
  if (n >= 50) return 3;
  if (n >= 45) return 2;
  if (n >= 40) return 1;
  return 0;
}

function toggleSection(id) {
  const el = document.getElementById(`${id}-section`);
  el.classList.toggle("hidden");
}

async function load() {
  // 1. Priority: Server-Injected State (SSR Hydration)
  if (window.serverAppState) {
    appState = window.serverAppState;
    // ensure activeSemIndex is set
    if (!appState.activeSemIndex) appState.activeSemIndex = 1;
    saveLocal(); // Sync local storage to match authoritative DB data
  } 
  // 2. Fallback: Local Storage
  else {
    const saved = localStorage.getItem("ns_final_v3");
    if (saved) {
      appState = JSON.parse(saved);
      if (!appState.activeSemIndex) appState.activeSemIndex = 1;
    }
  }

  // Try to sync from server
  try {
    const response = await fetch("sync");
    const result = await response.json();
    if (result.success && result.isLoggedIn) {
      document.getElementById("btn-login").classList.add("hidden");
      document.getElementById("logged-in-controls").classList.remove("hidden");

      if (result.data) {
        // Merge or overwrite? Let's overwrite for consistency with account
        appState = result.data;
        initTabs();
        switchLevel(appState.lvl);
        calc();
        saveLocal(); // sync local
      }
    }
  } catch (e) {
    console.error("Sync load failed", e);
  }
}

function saveLocal() {
  localStorage.setItem("ns_final_v3", JSON.stringify(appState));
}

// Global debounce timer
let saveTimeout;

function save() {
  saveLocal();

  // Auto-sync if logged in
  const loggedInControls = document.getElementById("logged-in-controls");
  if (loggedInControls && !loggedInControls.classList.contains("hidden")) {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
      saveRemote(true); // true means silent/background save
    }, 1500); // 1.5s debounce
  }
}

async function saveRemote(silent = false) {
  const btn = document.getElementById("btn-save");
  if (!btn) return;

  const originalText = btn.innerText;
  if (!silent) {
    btn.innerText = "Saving...";
    btn.disabled = true;
  } else {
    btn.classList.add("opacity-50");
  }

  try {
    const response = await fetch("sync", {
      method: "POST",
      body: JSON.stringify(appState),
    });
    const result = await response.json();
    if (result.success) {
      if (!silent) {
        btn.innerText = "Saved!";
        btn.classList.add("text-emerald-400");
      }
    } else if (!silent) {
      btn.innerText = "Error";
    }
  } catch (e) {
    if (!silent) btn.innerText = "Failed";
  } finally {
    if (!silent) {
      setTimeout(() => {
        btn.innerText = originalText;
        btn.disabled = false;
        btn.classList.remove("text-emerald-400");
      }, 2000);
    } else {
      btn.classList.remove("opacity-50");
    }
  }
}
function resetAll() {
  if (confirm("Delete all your academic entries?")) {
    localStorage.removeItem("ns_final_v3");
    appState = {
      lvl: 100,
      maxLvl: 400,
      db: {
        100: {
          1: {
            mode: "detail",
            type: "grade",
            items: [
              { u: "", g: "", s: "" },
              { u: "", g: "", s: "" },
              { u: "", g: "", s: "" },
              { u: "", g: "", s: "" },
            ],
          },
          2: {
            mode: "detail",
            type: "grade",
            items: [
              { u: "", g: "", s: "" },
              { u: "", g: "", s: "" },
              { u: "", g: "", s: "" },
              { u: "", g: "", s: "" },
            ],
          },
        },
      },
    };
    initTabs();
    switchLevel(100);
    calc();
  }
}

// Export PDF
async function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  const lvl = appState.lvl;
  const activeIdx = appState.activeSemIndex || 1;
  const s = appState.db[lvl]?.[activeIdx];

  // Branding Colors
  const primaryColor = [0, 86, 210]; // #0056D2
  
  // -- HEADER --
  doc.setFillColor(...primaryColor);
  doc.rect(0, 0, 210, 40, "F");
  
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(22);
  doc.setFont("helvetica", "bold");
  doc.text("Naija Cgpa", 105, 18, { align: "center" });
  
  doc.setFontSize(10);
  doc.setFont("helvetica", "normal");
  doc.text("Created by DevMeki", 105, 26, { align: "center" }); // Branding 1

  // -- DOC INFO --
  doc.setTextColor(50, 50, 50);
  doc.setFontSize(12);
  doc.setFont("helvetica", "bold");
  doc.text(`Level: ${lvl}L  |  ${activeIdx === 1 ? "First" : "Second"} Semester`, 14, 55);

  const date = new Date().toLocaleDateString();
  doc.setFont("helvetica", "normal");
  doc.text(`Date: ${date}`, 196, 55, { align: "right" });

  // -- TABLE --
  const tableColumn = ["Course Code", "Units", "Grade/Score", "Points"];
  const tableRows = [];

  let semUnits = 0;
  let semQP = 0;

  if (s) {
    if (s.mode === "detail") {
      s.items.forEach((item) => {
        if (item.u > 0) {
          let gp = -1;
          let displayGrade = "";
          
          if (s.type === "score") {
             gp = scoreToGP(item.s);
             displayGrade = item.s;
          } else {
             gp = item.g !== "" ? parseFloat(item.g) : -1;
             const letterMap = {5:'A', 4:'B', 3:'C', 2:'D', 1:'E', 0:'F'};
             displayGrade = letterMap[gp] || "-";
          }

          if (gp !== -1) {
            const points = item.u * gp;
            semUnits += item.u;
            semQP += points;
            tableRows.push([
                item.n || "Course",
                item.u,
                displayGrade,
                points
            ]);
          }
        }
      });
    } else if (s.mode === "quick" && s.units > 0) {
        semUnits = s.units;
        semQP = s.units * s.gpa;
        tableRows.push(["Quick Entry", s.units, `GPA: ${s.gpa}`, (semQP).toFixed(2)]);
    }
  }

  const semGPA = semUnits > 0 ? (semQP / semUnits).toFixed(2) : "0.00";

  // Use autoTable
  doc.autoTable({
    head: [tableColumn],
    body: tableRows,
    startY: 65,
    theme: 'grid',
    headStyles: { fillColor: primaryColor },
    styles: { font: "helvetica", fontSize: 10 },
  });

  // -- SUMMARY --
  let finalY = doc.lastAutoTable.finalY + 10;
  
  doc.setFontSize(10);
  doc.setTextColor(0, 0, 0);
  
  doc.text(`Total Semester Units: ${semUnits}`, 14, finalY);
  finalY += 6;
  doc.text(`Total Quality Points: ${semQP}`, 14, finalY);
  finalY += 8;
  
  doc.setFontSize(14);
  doc.setFont("helvetica", "bold");
  doc.setTextColor(...primaryColor);
  doc.text(`Semester GPA: ${semGPA}`, 14, finalY);

  // Global Context
  const globalCGPA = document.getElementById("global-cgpa").textContent;
  const globalClass = document.getElementById("global-class").textContent;
  
  finalY += 15;
  doc.setDrawColor(200, 200, 200);
  doc.line(14, finalY, 196, finalY);
  finalY += 10;

  doc.setFontSize(12);
  doc.setTextColor(50);
  doc.text(`Cumulative GPA (CGPA): ${globalCGPA}`, 14, finalY);
  doc.text(`Class: ${globalClass}`, 196, finalY, { align: "right" });

  // -- FOOTER --
  const pageCount = doc.internal.getNumberOfPages();
  for(let i = 1; i <= pageCount; i++) {
    doc.setPage(i);
    doc.setFontSize(8);
    doc.setTextColor(150);
    doc.text(`Naija Cgpa - Created by DevMeki`, 105, 290, { align: "center" }); // Branding 2
  }

  doc.save(`NaijaCgpa_${lvl}L_Sem${activeIdx}.pdf`);
  showToast("PDF Downloaded Successfully!");
}

// Export Image
async function exportImage() {
  const lvl = appState.lvl;
  const activeIdx = appState.activeSemIndex || 1;
  const s = appState.db[lvl]?.[activeIdx];
  const globalCGPA = document.getElementById("global-cgpa").textContent;
  const globalClass = document.getElementById("global-class").textContent;

  // Create temporary container
  const container = document.createElement("div");
  // Use off-screen positioning
  container.className = "fixed top-0 left-[-9999px] p-8 w-[600px] z-[100]";
  container.style.backgroundColor = "#ffffff"; // Explicit Hex
  container.style.fontFamily = "'Outfit', sans-serif"; // Ensure font

  // Build Results Table HTML
  let rowsHtml = "";
  let semUnits = 0;
  let semQP = 0;

  if (s) {
    if (s.mode === "detail") {
      s.items.forEach((item) => {
        if (item.u > 0) {
          let gp = -1;
          let displayGrade = "";
          if (s.type === "score") {
             gp = scoreToGP(item.s);
             displayGrade = item.s;
          } else {
             gp = item.g !== "" ? parseFloat(item.g) : -1;
             const letterMap = {5:'A', 4:'B', 3:'C', 2:'D', 1:'E', 0:'F'};
             displayGrade = letterMap[gp] || "-";
          }

          if (gp !== -1) {
            const points = item.u * gp;
            semUnits += item.u;
            semQP += points;
            rowsHtml += `
              <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 8px 0; font-size: 14px; font-weight: 700; color: #334155;">${item.n || "Course"}</td>
                <td style="padding: 8px 0; font-size: 14px; text-align: center; color: #475569;">${item.u}</td>
                <td style="padding: 8px 0; font-size: 14px; text-align: center; color: #475569;">${displayGrade}</td>
                <td style="padding: 8px 0; font-size: 14px; text-align: right; color: #334155;">${points}</td>
              </tr>
            `;
          }
        }
      });
    } else if (s.mode === "quick" && s.units > 0) {
        semUnits = s.units;
        semQP = s.units * s.gpa;
        rowsHtml += `
          <tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="padding: 8px 0; font-size: 14px; font-weight: 700; color: #334155;">Quick Entry</td>
            <td style="padding: 8px 0; font-size: 14px; text-align: center; color: #475569;">${s.units}</td>
            <td style="padding: 8px 0; font-size: 14px; text-align: center; color: #475569;">GPA: ${s.gpa}</td>
            <td style="padding: 8px 0; font-size: 14px; text-align: right; color: #334155;">${semQP.toFixed(2)}</td>
          </tr>
        `;
    }
  }

  const semGPA = semUnits > 0 ? (semQP / semUnits).toFixed(2) : "0.00";

  // Use inline styles for colors and borders to avoid oklch/var issues
  container.innerHTML = `
    <div style="border: 4px solid #4f46e5; border-radius: 24px; padding: 24px; background-color: #f8fafc;">
      <div style="text-align: center; margin-bottom: 8px;">
        <h1 style="font-size: 30px; font-weight: 800; color: #4338ca; margin: 0;">Naija Cgpa</h1>
        <p style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin: 4px 0 0 0;">Created by DevMeki</p>
      </div>
      
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 24px;">
        <div>
           <p style="font-size: 14px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 4px 0;">Level</p>
           <p style="font-size: 20px; font-weight: 900; color: #1e293b; margin: 0;">${lvl}L</p>
        </div>
        <div style="text-align: right;">
           <p style="font-size: 14px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 4px 0;">Semester</p>
           <p style="font-size: 20px; font-weight: 900; color: #1e293b; margin: 0;">${activeIdx === 1 ? "First" : "Second"}</p>
        </div>
      </div>

      <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <thead>
          <tr style="text-align: left; color: #94a3b8;">
            <th style="font-size: 12px; font-weight: 900; text-transform: uppercase; padding-bottom: 8px;">Course</th>
            <th style="font-size: 12px; font-weight: 900; text-transform: uppercase; padding-bottom: 8px; text-align: center;">Unit</th>
            <th style="font-size: 12px; font-weight: 900; text-transform: uppercase; padding-bottom: 8px; text-align: center;">Grd</th>
            <th style="font-size: 12px; font-weight: 900; text-transform: uppercase; padding-bottom: 8px; text-align: right;">Pnt</th>
          </tr>
        </thead>
        <tbody>${rowsHtml}</tbody>
      </table>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-top: 2px solid #e2e8f0; padding-top: 16px;">
        <div>
            <p style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 4px 0;">Sem GPA</p>
            <p style="font-size: 24px; font-weight: 900; color: #4f46e5; margin: 0;">${semGPA}</p>
        </div>
        <div style="text-align: right;">
            <p style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 4px 0;">Total CGPA</p>
            <p style="font-size: 24px; font-weight: 900; color: #059669; margin: 0;">${globalCGPA}</p>
        </div>
      </div>
      
      <div style="text-align: center; padding-top: 16px;">
         <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 16px; border-radius: 999px; font-size: 12px; font-weight: 900; text-transform: uppercase; display: inline-block;">${globalClass}</span>
      </div>
    </div>
  `;

  document.body.appendChild(container);

  try {
    // Small delay to ensure rendering
    await new Promise(r => setTimeout(r, 100));
    
    const canvas = await html2canvas(container, { 
        scale: 2, 
        backgroundColor: null,
        useCORS: true 
    });
    
    container.remove();

    const link = document.createElement('a');
    link.download = `NaijaCgpa_${lvl}L_Sem${activeIdx}.png`;
    link.href = canvas.toDataURL();
    link.click();
    
    showToast("Image Saved Successfully!");
  } catch (err) {
    console.error("Image Export Failed:", err);
    container.remove();
    showToast("Failed to save image");
  }
}

// Share Result
async function shareResult() {
  const btn = document.querySelector('button[onclick="shareResult()"]');
  const originalHtml = btn.innerHTML;
  btn.innerHTML = `<span class="text-[8px] font-extrabold uppercase">...</span>`;
  btn.disabled = true;

  const lvl = appState.lvl;
  const activeIdx = appState.activeSemIndex || 1;
  const s = appState.db[lvl]?.[activeIdx];
  const globalCGPA = document.getElementById("global-cgpa").textContent;
  const globalClass = document.getElementById("global-class").textContent;
  const semGPA = document.getElementById("sem-gpa-val").textContent;

  // Prepare Data
  const data = {
    lvl: lvl,
    activeIdx: activeIdx,
    semGPA: semGPA,
    globalCGPA: globalCGPA,
    globalClass: globalClass,
    items: []
  };

  if (s) {
     if (s.mode === "detail") {
       s.items.forEach(i => {
          if (i.u > 0) {
            let grade = "-";
            if (s.type === "score") grade = i.s; // Just show score
            else {
               const gp = i.g !== "" ? parseFloat(i.g) : -1;
               const letterMap = {5:'A', 4:'B', 3:'C', 2:'D', 1:'E', 0:'F'};
               grade = letterMap[gp] || "-";
            }
            data.items.push({ name: i.n, u: i.u, grade: grade });
          }
       });
     } else if (s.mode === "quick" && s.units > 0) {
         data.items.push({ name: "Quick Entry", u: s.units, grade: `GPA: ${s.gpa}` });
     }
  }

  // Send to Backend
  try {
    const response = await fetch('share.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    });
    const result = await response.json();

    if (result.success) {
        const link = `${window.location.origin}/Naijacgpa/view.php?id=${result.uuid}`;
        
        // Populate Modal
        document.getElementById('share-link').value = link;
        
        // Update Social Links
        const text = `Check out my ${lvl}L academic performance on Naija Cgpa! Semester GPA: ${semGPA}, CGPA: ${globalCGPA}`;
        const encodedText = encodeURIComponent(text);
        const encodedLink = encodeURIComponent(link);

        document.getElementById('share-wa').href = `https://wa.me/?text=${encodedText}%20${encodedLink}`;
        document.getElementById('share-tw').href = `https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedLink}`;
        document.getElementById('share-fb').href = `https://www.facebook.com/sharer/sharer.php?u=${encodedLink}`;
        document.getElementById('share-li').href = `https://www.linkedin.com/sharing/share-offsite/?url=${encodedLink}`;

        // Show Modal
        document.getElementById('share-modal').classList.remove('hidden');
    } else {
        showToast("Error generating link");
    }
  } catch (error) {
    console.error('Error:', error);
    showToast("Check your connection");
  } finally {
    btn.innerHTML = originalHtml;
    btn.disabled = false;
  }
}

function copyShareLink() {
    const copyText = document.getElementById("share-link");
    copyText.select();
    copyText.setSelectionRange(0, 99999); 
    navigator.clipboard.writeText(copyText.value);
    
    const btn = document.querySelector('button[onclick="copyShareLink()"]');
    const originalText = btn.innerText;
    btn.innerText = "COPIED!";
    setTimeout(() => btn.innerText = originalText, 2000);
}

function showToast(msg) {
  const toast = document.getElementById("toast");
  const tMsg = document.getElementById("toast-msg");
  if (!toast || !tMsg) return;

  tMsg.textContent = msg;
  toast.classList.remove("hidden");
  // Trigger reflow
  void toast.offsetWidth;
  toast.classList.remove("opacity-0");

  setTimeout(() => {
    toast.classList.add("opacity-0");
    setTimeout(() => {
      toast.classList.add("hidden");
    }, 300);
  }, 3000);
}
