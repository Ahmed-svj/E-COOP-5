<?php $title = __('nav.reports'); ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.reports'), 'sub' => __('sup.reports_sub')]) ?>
<?php if (!$items): ?><?= View::partial('partials/empty', ['icon' => 'doc', 'text' => __('sup.no_reports')]) ?>
<?php else: ?><div class="co-card"><div class="co-list">
<?php foreach ($items as $r): ?>
  <div class="co-row app-row-wrap"><span class="co-row__media"><?= icon('doc') ?></span>
    <div class="co-row__text"><span class="co-row__title"><?= e($r['FullName']) ?> <span class="co-caption" dir="ltr"><?= e($r['InstitutionalID']) ?></span></span><span class="co-row__sub"><?= e(__('report.submitted_on', ['date' => fmt_date($r['SubmittedAt'], true)])) ?> · <?= e(__('status.' . $r['Status'])) ?></span>
      <?php if ($r['ReviewComments']): ?><span class="co-row__sub"><?= e($r['ReviewComments']) ?></span><?php endif; ?></div>
    <div class="co-row__actions"><a class="co-btn co-btn--soft co-btn--sm" href="<?= url('/files/report/' . $r['ReportID']) ?>"><?= icon('download', 'co-ico--sm') ?><?= e(__('common.download')) ?></a>
      <?php if ($r['ReviewStatus'] === 'Pending'): ?>
        <button class="co-btn co-btn--danger-ghost co-btn--sm" type="button" data-bs-toggle="modal" data-bs-target="#rev-<?= (int) $r['ReportID'] ?>"><?= e(__('report.request_revision')) ?></button>
        <form method="post" action="<?= url('/supervisor/reports/' . $r['ReportID'] . '/review') ?>"><?= csrf_field() ?><input type="hidden" name="decision" value="Approved"><button class="co-btn co-btn--primary co-btn--sm"><?= e(__('common.approve')) ?></button></form>
      <?php else: ?><?= status_badge($r['ReviewStatus']) ?><?php endif; ?></div></div>
  <?php if ($r['ReviewStatus'] === 'Pending'): ?>
  <div class="modal fade" id="rev-<?= (int) $r['ReportID'] ?>" tabindex="-1" aria-labelledby="rev-<?= (int) $r['ReportID'] ?>-t" aria-hidden="true"><div class="modal-dialog modal-dialog-centered">
    <form class="modal-content app-modal" method="post" action="<?= url('/supervisor/reports/' . $r['ReportID'] . '/review') ?>"><?= csrf_field() ?><input type="hidden" name="decision" value="Revision Requested">
      <div class="modal-header"><h2 class="co-card__title" id="rev-<?= (int) $r['ReportID'] ?>-t"><?= e(__('report.request_revision')) ?> · <?= e($r['FullName']) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= e(__('a11y.close')) ?>"></button></div>
      <div class="modal-body co-field"><label for="rc-<?= (int) $r['ReportID'] ?>"><?= e(__('report.what_to_change')) ?></label><textarea class="co-input app-textarea" id="rc-<?= (int) $r['ReportID'] ?>" name="comments" rows="4" required maxlength="3000"></textarea></div>
      <div class="modal-footer"><button type="button" class="co-btn co-btn--soft co-btn--sm" data-bs-dismiss="modal"><?= e(__('common.cancel')) ?></button><button class="co-btn co-btn--primary co-btn--sm" type="submit"><?= e(__('report.send_back')) ?></button></div>
    </form></div></div>
  <?php endif; ?>
<?php endforeach; ?>
</div></div><?php endif; ?>
