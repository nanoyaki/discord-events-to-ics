{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "1.0.0";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-mc5cjk1MvCoYCULFB454iO/0okj/tIE5x5PtHWzQ5PI=";
}
