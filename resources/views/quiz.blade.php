@extends('layouts.app')

@section('content')
  <header class="gible-page-hero gible-reveal mb-4">
    <span class="gible-kicker">Check your understanding</span>
    <h1>Quiz generator</h1>
    <p class="gible-lead">Create a quick multiple-choice quiz from your latest topic summary.</p>
  </header>

  <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-1">
    <div class="card-body p-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-6">
          <label class="form-label"><i class="bi bi-tag me-1 text-info"></i>Topic label</label>
          <input id="topicField" class="form-control" maxlength="160" autocomplete="off" placeholder="Binary search tree" aria-describedby="topicFieldHelp" />
        </div>
        <div class="col-md-6 d-flex flex-wrap gap-2 align-items-center">
          <button type="button" class="btn btn-outline-secondary" id="loadLast"><i class="bi bi-arrow-counterclockwise me-1"></i>Use last search</button>
          <button type="button" class="btn btn-primary" id="genBtn"><i class="bi bi-magic me-1"></i>Generate quiz</button>
          <span id="topicFieldHelp" class="small text-secondary w-100 mb-0 mb-md-n1">Uses your latest Topic Search summary and creates around 10 questions.</span>
        </div>
      </div>
    </div>
  </div>

  <div id="quizAlert"></div>
  <form id="quizForm" class="d-none">
    @csrf
    <div id="questionsMount"></div>
    <button type="submit" class="btn btn-success btn-lg mt-2" id="quizSubmitBtn"><i class="bi bi-send-fill me-2"></i>Submit answers</button>
  </form>
  <div id="resultBox" class="mt-4"></div>
@endsection

@push('scripts')
  <script>
    const searchUrl = @json(route('search'));
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let serverQuestions = [];
    const topicField = document.getElementById('topicField');
    document.getElementById('loadLast').addEventListener('click', function () {
      try {
        topicField.value = sessionStorage.getItem('gible_lastTopic') || '';
      } catch (e) {}
    });
    document.getElementById('genBtn').addEventListener('click', async function () {
      const genBtn = document.getElementById('genBtn');
      document.getElementById('quizAlert').innerHTML = '';
      let extract = '';
      try {
        extract = sessionStorage.getItem('gible_lastExtract') || '';
      } catch (e) {}
      const topic = topicField.value.trim() || 'Topic';
      if (!extract) {
        document.getElementById('quizAlert').innerHTML =
          '<div class="alert alert-warning">No saved summary found. Open <a href="' + searchUrl + '">Topic Search</a> and search a topic first.</div>';
        return;
      }
      genBtn.disabled = true;
      try {
        const res = await fetch('/api/quiz/generate', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
          body: JSON.stringify({ topic: topic, extract: extract }),
        });
        const data = await res.json();
        if (!res.ok) {
          document.getElementById('quizAlert').innerHTML =
            '<div class="alert alert-danger">' + (data.error || 'Could not generate quiz') + '</div>';
          return;
        }
        serverQuestions = data.questions;
      const mount = document.getElementById('questionsMount');
      mount.innerHTML = '';
      data.questions.forEach(function (q, qi) {
        const block = document.createElement('div');
        block.className = 'card gible-card border-0 shadow-sm mb-3';
        const opts = q.options
          .map(function (opt, oi) {
            return (
              '<div class="form-check mb-2 py-1"><input class="form-check-input" type="radio" name="q' +
              qi +
              '" value="' +
              oi +
              '" id="q' +
              qi +
              'o' +
              oi +
              '"/><label class="form-check-label ms-1" for="q' +
              qi +
              'o' +
              oi +
              '">' +
              String(opt).replace(/</g, '&lt;') +
              '</label></div>'
            );
          })
          .join('');
        block.innerHTML =
          '<div class="card-body"><div class="d-flex align-items-center gap-2 mb-2"><span class="badge rounded-pill bg-primary">Q' +
          (qi + 1) +
          '</span><span class="small text-secondary">Select the best match</span></div><p class="fw-medium mb-3">' +
          String(q.question).replace(/</g, '&lt;') +
          '</p>' +
          opts +
          '</div>';
        mount.appendChild(block);
      });
        document.getElementById('quizForm').classList.remove('d-none');
        document.getElementById('resultBox').innerHTML = '';
      } catch (err) {
        document.getElementById('quizAlert').innerHTML =
          '<div class="alert alert-danger">Could not reach the server. Check your connection and try again.</div>';
      } finally {
        genBtn.disabled = false;
      }
    });
    document.getElementById('resultBox').addEventListener('click', function (e) {
      if (!e.target.closest('#retakeQuizBtn')) return;
      document.querySelectorAll('#quizForm input[type="radio"]').forEach(function (r) {
        r.checked = false;
      });
      document.getElementById('quizForm').classList.remove('d-none');
      document.getElementById('resultBox').innerHTML = '';
      document.getElementById('quizForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    document.getElementById('quizForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      var submitBtn = document.getElementById('quizSubmitBtn');
      var answers = [];
      var ok = true;
      serverQuestions.forEach(function (_q, qi) {
        var picked = document.querySelector('input[name="q' + qi + '"]:checked');
        if (!picked) ok = false;
        else answers.push(Number(picked.value));
      });
      if (!ok) {
        document.getElementById('resultBox').innerHTML = '<div class="alert alert-warning">Answer every question.</div>';
        return;
      }
      var topic = topicField.value.trim() || 'Topic';
      submitBtn.disabled = true;
      try {
        var res = await fetch('/api/quiz/submit', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
          body: JSON.stringify({ topic: topic, questions: serverQuestions, answers: answers }),
        });
        var data = await res.json();
        if (!res.ok) {
          document.getElementById('resultBox').innerHTML =
            '<div class="alert alert-danger">' + escapeHtml(data.error || 'Could not submit quiz.') + '</div>';
          return;
        }
        var discordLine =
          typeof data.webhook_delivered === 'boolean'
            ? '<p class="small text-secondary mb-0 mt-3"><i class="bi bi-send-check me-1"></i>Discord update: <strong>' +
              (data.webhook_delivered ? 'sent' : 'not sent right now') +
              '</strong></p>'
            : '';
        document.getElementById('quizForm').classList.add('d-none');
        document.getElementById('resultBox').innerHTML =
          '<div class="card gible-card border-0 shadow-sm">' +
          '<div class="card-body p-4">' +
          '<div class="d-flex align-items-start gap-3 mb-4 flex-wrap">' +
          '<div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:3rem;height:3rem" aria-hidden="true">' +
          '<i class="bi bi-check-lg fs-4"></i></div>' +
          '<div class="min-w-0">' +
          '<h2 class="h5 mb-2">Quiz complete</h2>' +
          '<p class="text-secondary small mb-0">Your answers are submitted and saved to your dashboard. Want another pass? Retake resets your choices so you can try again with this same quiz.</p>' +
          '</div></div>' +
          '<div class="d-flex align-items-center gap-3 flex-wrap">' +
          '<div class="fs-2 fw-bold text-info mb-0">' +
          escapeHtml(String(data.score)) +
          '</div><div><div class="text-secondary small text-uppercase fw-semibold">Score</div>' +
          '<div class="fs-4 fw-semibold">out of ' +
          escapeHtml(String(data.total)) +
          '</div></div></div>' +
          discordLine +
          '<div class="d-flex flex-wrap gap-2 mt-4">' +
          '<button type="button" class="btn btn-primary" id="retakeQuizBtn"><i class="bi bi-arrow-counterclockwise me-1"></i>Retake this quiz</button>' +
          '</div>' +
          '</div></div>';
      } catch (err) {
        document.getElementById('resultBox').innerHTML =
          '<div class="alert alert-danger">Could not reach the server. Check your connection and try again.</div>';
      } finally {
        submitBtn.disabled = false;
      }
    });
    function escapeHtml(s) {
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }
  </script>
@endpush
