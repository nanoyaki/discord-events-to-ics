{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "0.4.1";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-b4fbGZXPDj5ywu+UUaIi1TmnhJcbcmm3TrszTWVuOh4=";
}
