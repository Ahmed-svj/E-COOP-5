<?php $title = __('nav.report'); ?>
<?= View::partial('partials/page-head', ['heading' => __('report.title'), 'sub' => __('report.sub')]) ?>
<div class="row g-3"><div class="col-lg-7">
<section class="co-card">
  <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('doc') ?></span><h2 class="co-card__title"><?= e(__('report.status')) ?></h2></div>
  <?php if ($report): ?>
    <div class="co-row"><span class="co-row__media"><?= icon('doc') ?></span><div class="co-row__text"><span class="co-row__title"><?= e(__('report.file')) ?></span><span class="co-row__sub"><?= e(__('report.submitted_on', ['date' => fmt_date($report['SubmittedAt'], true)])) ?> · <?= e(__('status.' . $report['Status'])) ?></span></div>
      <div class="co-row__actions"><?= status_badge($report['ReviewStatus']) ?><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/files/report/' . $report['ReportID']) ?>"><?= icon('download', 'co-ico--sm') ?><?= e(__('common.download')) ?></a></div></div>
    <?php if ($report['ReviewComments']): ?><div class="co-alert<?= $report['ReviewStatus'] === 'Revision Requested' ? ' co-alert--amber' : ' co-alert--mint' ?>"><?= icon('chat') ?><div><strong><?= e(__('report.supervisor_comments')) ?></strong><?= nl2br(e($report['ReviewComments'])) ?></div></div><?php endif; ?>
  <?php else: ?>
    <p class="co-caption app-body-text"><?= e(__('report.none')) ?></p>
  <?php endif; ?>

  <?php if (!$s['PlacementID'] || !$s['SupervisorID']): ?>
    <div class="co-alert co-alert--amber"><?= icon('info') ?><div><?= e(__('report.not_ready')) ?></div></div>
  <?php elseif (!$report || $report['ReviewStatus'] === 'Revision Requested'): ?>
    <form method="post" action="<?= url('/student/report') ?>" enctype="multipart/form-data" class="app-form">
      <?= csrf_field() ?>
      <div class="co-field"><label for="report"><?= e($report ? __('report.reupload') : __('report.upload')) ?></label>
        <input class="co-input app-file" id="report" type="file" name="report" required accept=".pdf,.docx" data-max="<?= config('upload_max_bytes') ?>"><span class="co-field__help"><?= e(__('upload.help')) ?></span></div>
      <div><button class="co-btn co-btn--primary" type="submit"><?= icon('upload', 'co-ico--sm') ?><?= e(__('report.submit')) ?></button></div>
    </form>
  <?php endif; ?>
</section></div>
<div class="col-lg-5"><section class="co-card">
  <div class="co-card__head"><span class="co-tile"><?= icon('info') ?></span><h2 class="co-card__title"><?= e(__('report.rules')) ?></h2></div>
  <ul class="app-list"><li><?= e(__('report.rule1')) ?></li><li><?= e(__('report.rule2')) ?></li><li><?= e(__('report.rule3')) ?></li></ul>
  <?php if ($placement): ?><p class="co-caption"><?= e(__('dash.time_elapsed_due', ['date' => fmt_date($placement['EndDate'])])) ?></p><?php endif; ?>
</section></div></div>
